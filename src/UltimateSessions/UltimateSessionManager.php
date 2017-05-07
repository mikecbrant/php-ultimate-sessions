<?php

namespace MikeBrant\UltimateSessions;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Class UltimateSessionManager
 *
 * This class an object-oriented wrapper around common PHP session management
 * functions along with features geared at enchaning security
 * and proper session management behaviors including:
 * - timestamp-based management for session data expiry;
 * - automated session ID regeneration at configurable time- and count-based
 * intervals;
 * - client fingerprinting based on request header properties
 * - logging of data for certain use cases where there are session accesses
 * against expired data or accesses with mis-matched fingerprints, which may
 * need further investigation. Only data within session metadata is logged.
 * An optional PSR-3 compliant logger to be used for logging in lieu of
 * default logging via error_log().
 *
 * For cases where custom session garbage collection is implemented
 * (something that should strongly be considered for production-level
 * applications), this class offers setting of an optional callback that
 * is passed session ID and data expiry timestamp information such that code
 * that provides the callback can be notified of session ID regeneration and
 * session destroy events so as to be able to mark those session ID's as
 * eligible for garbage collection after given timestamp (by touching files,
 * updating database field, etc.).
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionManager implements LoggerAwareInterface
{
    /**
     * @var string Key to which the metadata object will be set within
     * $_SESSION superglobal
     */
    const METADATA_KEY = 'ultSessionMetadata';

   /**
     * Stores UltimateSessionHandlerConfig object as passed from
     * UltimateSessionHandler.
     *
     * @var UltimateSessionManagerConfig
     */
    protected $config;

    /**
     * An UltimateSessionManagerMetadata object which stores all the session
     * metadata that UltimateSessionManager injects into $_SESSION to manage
     * session security.
     *
     * @var UltimateSessionManagerMetadata
     */
    protected $metadata;

    /**
     * Property which stores an optional callback for use when implementing
     * session handlers which require custom garbage collection logic. This
     * callback can be used to mark a particular session ID as eligible for
     * garbage collection after a given timestamp.  This callable should have
     * the following signature:
     *
     * function (string $sessionId, int $expiryTimestamp) { ... }
     *
     * @var callable
     */
    protected $gcNotificationCallback;

    /**
     * Stores optional PSR-3 compliant logger object
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * UltimateSessionManager constructor.
     *
     * This method requires injection of UltimateSessionManagerConfig object.
     *
     * Since default configuration values should suffice for most use
     * cases, the following would be typical usage to instantiate configuration
     * into UltimateSessionManager:
     *
     * $session = new UltimateSessionManager(new UltimateSessionManagerConfig);
     *
     * This method also supports optional injection of a PSR-3 compliant
     * logger adhering to Psr\Log\LoggerInterface
     *
     * @param UltimateSessionManagerConfig $config
     * @param callable|null $gcNotificationCallback
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        UltimateSessionManagerConfig $config,
        callable $gcNotificationCallback = null,
        LoggerInterface $logger = null
    ) {
        $this->config = $config;
        if(!is_null(($gcNotificationCallback))) {
            $this->gcNotificationCallback = $gcNotificationCallback;
        }
        if(!is_null($logger)) {
            $this->setLogger($logger);
        }
    }

    /**
     * Method to start session using session_start() and inject security
     * metadata into $_SESSION. Method returns true on success and false on
     * failure.  If this method throws or returns false, you should consider
     * this an insecure session and remove any elevated privileges requestor
     * may have (i.e. consider them un-authenticated).
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function startSession()
    {
        if (!empty($this->config->sessionName)) {
            session_name($this->config->sessionName);
        }
        $result = session_start();
        if ($result === false) {
            throw new \RuntimeException('session_start() unexpectedly failed.');
        }
        if (empty($_SESSION)) {
            return $this->initializeSession();
        }
        $this->metadata = $_SESSION[self::METADATA_KEY];
        if(!$this->isSessionValid()) {
            $msg = "Session ID '" . session_id() . "' invalidated. JSON metadata dump:\n"
                . json_encode($this->metadata, JSON_PRETTY_PRINT) . "\n";
            $this->log($msg);
            $this->destroySession();
            return false;
        }
        return $this->continueSession();
    }

    /**
     * Method used to instantiate a new set of metadata into the session as
     * would typically happen on first session start or after session ID
     * regeneration events.
     *
     * @return bool
     */
    protected function initializeSession()
    {
        $this->metadata = new UltimateSessionManagerMetadata();
        $this->metadata->regenerateIdAt = $this->metadata->instantiatedAt +
            ($this->config->regenIdInterval * 60);
        $this->metadata->fingerprint = $this->generateFingerprint();
        $this->writeMetadata();
        return true;
    }

    /**
     * Method which continues a session that is considered valid, checking
     * whether that session either needs to be forwarded to a new session ID
     * based on recent session ID regeneration event, or whether the session
     * has exceeded configured threshold for a forced session ID regeneration.
     * If neithed of these cases is true, this method simple increments the
     * session start counter in session metadata.
     *
     * @return bool
     */
    protected function continueSession()
    {
        if($this->isForwardedSession()) {
            return $this->forwardSession($this->metadata->forwardToSessionId);
        }
        if($this->needsIdRegen()) {
            return $this->regenerateId();
        }
        $this->metadata->sessionStartCount++;
        $this->writeMetadata();
        return true;
    }

    /**
     * This method generates new session ID while also writing data expiry
     * and session ID forwarding information to current session. This method
     * calls forwardSession() method with generated ID to actually trigger
     * session ID change. This method should be used by external callers to
     * trigger session ID regeneration after critical state changes in the
     * application such as login/logout, privilege escalation, etc.
     *
     * @return bool
     */
    public function regenerateId()
    {
        $this->metadata->isActive = false;
        $this->metadata->expireDataAt = time() + $this->config->ttlAfterIdRegen;
        $newSessionId = session_create_id();
        $this->metadata->forwardToSessionId = $newSessionId;
        $this->writeMetadata();
        $this->executeGcNotificationCallback(
            session_id(),
            $this->metadata->expireDataAt
        );
        return $this->forwardSession($newSessionId);
    }

    /**
     * This method accepts a session ID as generated by session_create_id(),
     * closes existing session and then initiates new session with passed
     * session ID.
     *
     * @param string $newSessionId
     * @return bool
     */
    protected function forwardSession($newSessionId)
    {
        session_write_close();

        session_id($newSessionId);
        ini_set('session.use_strict_mode', 0);
        session_start();
        ini_set('session.use_strict_mode', 1);
        return $this->initializeSession();
    }

    /**
     * Method to fully unset all session data and close session. Note this
     * method does not destroy the session cookie, which is not necessary
     * when running session using strict mode, which is a baseline requirement
     * for using this library.
     *
     * This method can be used by external callers to immediately disable all
     * data access to an existing session and close the session for rare
     * cases (i.e. expected security breach) where application logic may
     * dictate such usage.
     */
    public function destroySession()
    {
        $_SESSION = [];
        $this->executeGcNotificationCallback(session_id(), time());
        session_destroy();
    }

    /**
     * Method which determines if current session id valid by verifying the
     * session fingerprint as well as whether the session is either the
     * currently active session or a session that is eligible to be forwarded
     * to a currently active session based on current timestamp being less
     * than the data expiry timestamp of session on which ID regeneration has
     * been performed.
     *
     * @return bool
     * @throws \RuntimeException
     */
    protected function isSessionValid() {
        if(empty($this->metadata)) {
            throw new \RuntimeException(
                "Session metadata key is missing from SESSION superglobal."
            );
        }
        return (
            $this->isValidFingerprint() === true &&
            (
                $this->metadata->isActive === true ||
                $this->isForwardedSession()
            )
        );
    }

    /**
     * Method which compares session fingerprint hash in session metadata
     * against hash formed from current request to determine if the session
     * cna reasonable be expected to be from the same client.
     *
     * @return bool
     */
    protected function isValidFingerprint() {
        $requestFingerprint = $this->generateFingerprint();
        if ($this->metadata->fingerprint !== $requestFingerprint) {
            $this->log(
                "Fingerprint mismatch.\nFingerprint in session: '" .
                $this->metadata->fingerprint . "'\nFingerprint from request: '" .
                $requestFingerprint . "'\n"
            );
            return false;
        }
        return true;
    }

    /**
     * Method which uses session metadata to determine if current request is
     * coming from session ID that has been regenerated and is still within
     * expiry TTL such that it can be forwarded to new session.
     *
     * @return bool
     */
    protected function isForwardedSession() {
        return (
            $this->metadata->isActive === false &&
            time() < $this->metadata->expireDataAt &&
            !empty($this->metadata->forwardToSessionId)
        );
    }

    /**
     * Method which uses session metadata to determine of current session ID
     * should be regenerated either because the session regeneration
     * timestamp has passed or the number of session_start() calls has
     * exceeded the configured threshold.
     *
     * @return bool
     */
    protected function needsIdRegen() {
        return (
            $this->metadata->regenerateIdAt <= time() ||
            $this->metadata->sessionStartCount >= $this->config->regenIdCount
        );
    }

    /**
     * Method to persist the metadata object to $_SESSION superglobal.
     */
    protected function writeMetadata() {
        $_SESSION[self::METADATA_KEY] = $this->metadata;
    }

    /**
     * Method to generate a fingerprint hash from client information
     * available in $_SERVER superglobal. This hash should be considered to
     * be stable for a client during any given session.
     *
     * @return string
     */
    protected function generateFingerprint()
    {
        $fingerprint = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['HTTP_ACCEPT_ENCODING']
            . $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        return hash('sha56', $fingerprint);
    }

    /**
     * Method which executes garbage collection notification callback if set
     * on object.  This method will forward the session ID and data expiry
     * timestamp passed as parameters to the callback.
     *
     * @param $sessionId
     * @param $expiryTimestamp
     */
    protected function executeGcNotificationCallback($sessionId, $expiryTimestamp)
    {
        if(!is_null($this->gcNotificationCallback)) {
            call_user_func(
                $this->gcNotificationCallback,
                $sessionId,
                $expiryTimestamp
            );
        }
    }

    /**
     * Method to allow optional setting of PSR-3 compliant logger for session
     * logging.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logging method.
     *
     * @param string $message
     * @param string $level
     * @return bool
     */
    protected function log($message, $level = LogLevel::NOTICE)
    {
        if(!empty($this->logger)) {
            $this->logger->log($level, $message);
            return true;
        }
        return error_log($message, 0);
    }
}