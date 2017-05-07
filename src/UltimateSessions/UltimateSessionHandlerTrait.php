<?php

namespace MikeBrant\UltimateSessions;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Trait UltimateSessionHandlerTrait
 *
 * This trait is intended to be composed into any class which implements the
 * UltimateSessionHandlerInterface and has a dependency on that interface, so
 * must be used jointly.
 *
 * This trait exposes basic functionality for:
 *
 * - creating and storing session configuration;
 * - setting appropriate php.ini session.* settings based on constants in
 * interface;
 * - handling encryption/decryption of session data, including encryption key
 * generation;
 * - setting and destroying cookie which stores encryption key with the client;
 * - setting the object using the trait as session handler via
 * session_set_save_handler().
 *
 * @package MikeBrant\UltimateSessions
 */
trait UltimateSessionHandlerTrait
{
    /**
     * Object storing session handler configuration values as derived from an
     * UltimateSessionHandleConfig object and needed for classes inheriting
     * UltimateSessionHandlerTrait.
     *
     * @var UltimateSessionHandlerConfig Session handler configuration
     */
    protected $config = null;

    /**
     * Encryption key object as returned by
     * Defuse\Crypto\Key::createNewRandomKey()
     *
     * @var Key
     */
    protected $encryptionKey = null;

    /**
     * Method to initialize session handler.  This method must be called in
     * constructor from any class inheriting UltimateSessionHandlerTrait.
     *
     * @return void
     * @throws \Exception
     */
    public function sessionHandlerInit()
    {
        try {
            $this->configureIniSettings();
        } catch (\Exception $e) {
            throw new \Exception(
                "Unable to establish session using Ultimate Sessions library. " .
                "Please see previous exception in chain for details.",
                0,
                $e
            );
        }
        /** @noinspection PhpParamsInspection */
        $this->setSessionHandler($this);
    }

    /**
     * Method to validate session ID values for use by classes implementing
     * UltimateSessionHandlerInterface.
     *
     * @param $sessionId
     * @return void
     * @throws \InvalidArgumentException
     */
    public function validateSessionId($sessionId)
    {
        $regex = UltimateSessionHandlerInterface::SESSION_ID_REGEX_PHP_BELOW_7_1_0;
        if(version_compare(PHP_VERSION, '7.1.0', '>=')) {
            $regex = UltimateSessionHandlerInterface::SESSION_ID_REGEX_PHP_7_1_0;
        }
        if(!preg_match($regex, $sessionId)) {
            throw new \InvalidArgumentException('Value did not match session ID regex.');
        }
    }

    /**
     * Method to set ASCII encryption key to cookie based on values derived from
     * configuration. This method both sends header via setcookie() and sets
     * $_COOKIE.
     *
     * @param $sessionId
     * @param $asciiKey
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setEncryptionKeyCookie($sessionId, $asciiKey)
    {
        $this->validateSessionId($sessionId);
        if(empty($asciiKey) || !is_string($asciiKey)) {
            throw new \InvalidArgumentException(
                'Value must be non-zero length string'
            );
        }
        $cookieName = $this->config->keyCookiePrefix . $sessionId;
        setcookie(
            $cookieName,
            $asciiKey,
            $this->config->cookieLifetime,
            $this->config->cookiePath,
            $this->config->cookieDomain,
            $this->config->cookieSecure,
            true
        );
        $_COOKIE[$cookieName] = $asciiKey;
    }

    /**
     * Method to delete ASCII encryption key cookie. This method both sends
     * expired cookie header via setcookie and unsets the key from $_COOKIE.
     *
     * @param $sessionId
     * @return void
     * @throws \InvalidArgumentException
     */
    public function deleteEncryptionKeyCookie($sessionId)
    {
        $this->validateSessionId($sessionId);
        $cookieName = $this->config->keyCookiePrefix . $sessionId;
        setcookie(
            $cookieName,
            null,
            -1,
            $this->config->cookiePath,
            $this->config->cookieDomain,
            $this->config->cookieSecure,
            true
        );
        unset($_COOKIE[$cookieName]);
    }

    /**
     * Method to perform encryption of session data.
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \InvalidArgumentException
     */
    public function encrypt($sessionId, $sessionData)
    {
        $key = $this->getEncryptionKey($sessionId);
        return Crypto::encrypt($sessionData, $key);
    }

    /**
     * Method that provides guaranteed return of Defuse\Crypto\Key object for
     * use in encryption.
     *
     * @param $sessionId
     * @return Key
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \InvalidArgumentException
     */
    public function getEncryptionKey($sessionId)
    {
        if($this->encryptionKey instanceof Key === false) {
            return $this->setEncryptionKey($sessionId);
        }
        return $this->encryptionKey;
    }

    /**
     * Method which instantiates Defuse\Crypto\Key object and sets it on
     * encrpytionKey property. Key can either be recovered from encryption
     * key cookie or created from scratch.
     *
     * @param $sessionId
     * @return Key
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \InvalidArgumentException
     */
    public function setEncryptionKey($sessionId)
    {
        $cookieName = $this->config->keyCookiePrefix . $sessionId;
        if(!empty($_COOKIE[$cookieName])) {
            $this->encryptionKey = Key::loadFromAsciiSafeString($_COOKIE[$cookieName]);
            return $this->encryptionKey;
        }
        $this->encryptionKey = Key::createNewRandomKey();
        $this->setEncryptionKeyCookie(
            $sessionId,
            $this->encryptionKey->saveToAsciiSafeString()
        );
        return $this->encryptionKey;
    }

    /**
     * Method to perform decryption of session data.
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     * @throws \InvalidArgumentException
     */
    public function decrypt($sessionId, $sessionData)
    {
        $key = $this->getEncryptionKey($sessionId);
        return Crypto::decrypt($sessionData, $key);
    }

    /**
     * Make sure environment has proper php.ini settings for sessions.
     * Provide appropriate settings for session ID hash (PHP < 7.1) or random ID
     * generation (PHP 7.1+).
     *
     * @return void
     * @throws \RuntimeException
     */
    private function configureIniSettings()
    {
        /**
         * Ignore these lines from code coverage as they are covered in build
         * tests against different PHP versions.
         *
         * @codeCoverageIgnoreStart
         */
        $requiredIniSettings = array_merge(
            UltimateSessionHandlerInterface::REQUIRED_INI_SETTINGS,
            UltimateSessionHandlerInterface::REQUIRED_INI_SETTINGS_PHP_BELOW_7_1_0
        );
        if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
            $requiredIniSettings = array_merge(
                UltimateSessionHandlerInterface::REQUIRED_INI_SETTINGS,
                UltimateSessionHandlerInterface::REQUIRED_INI_SETTINGS_PHP_7_1_0
            );
        }
        /** @codeCoverageIgnoreEnd */

        foreach($requiredIniSettings as $setting => $value) {
            if (ini_get($setting) === $value) {
                continue;
            }
            if (ini_set($setting, $value) === false) {
                throw new \RuntimeException(
                    "Unable to set php.ini setting '{$setting}' to '{$value}'. " .
                    "This is a requirement for secure sessions."
                );
            }
        }
    }

    /**
     * Method that sets the UltimateSessionHandlerInterface object to
     * session_set_save_handler().
     *
     * @param UltimateSessionHandlerInterface $handler
     * @return void
     */
    private function setSessionHandler(UltimateSessionHandlerInterface $handler)
    {
        session_set_save_handler($handler);
    }
}