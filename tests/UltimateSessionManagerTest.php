<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * Mock for error_log() functionality to simply echo message to screen for
 * use with PHPUnit expectOutputString. This allows us to test logging paths
 * in code
 *
 * @param $message
 * @param int $type
 */
function error_log($message, $type = 0)
{
    echo $message . ':' . (string)$type . "\n";
}

/**
 * Class SimpleLogger
 *
 * A simple PSR-3 compliant logger to allow attaching as logger to
 * UltimateSessionHandler.  This uses error_log() as logging mechanism so
 * also uses mock function defined above.
 *
 * @package MikeBrant\UltimateSessions
 */
class SimpleLogger extends AbstractLogger
{
    public function log($level, $message, array $context = array())
    {
        error_log($level . ':' . $message, 0);
    }
}

/**
 * Class UltimateSessionManagerTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionManagerTest extends TestCase
{
    /**
     * Property to store current session manager under test.
     *
     * @var UltimateSessionManager;
     */
    protected $manager;

    /**
     * @return array
     */
    public function configValues()
    {
        return [
            new UltimateSessionManagerConfig(),
            new UltimateSessionManagerConfig(0),
            new UltimateSessionManagerConfig(null, 0),
            new UltimateSessionManagerConfig(0, 0)
        ];
    }

    /**
     * @return array
     */
    public function useGcCallbackValues()
    {
        return [
            false,
            true
        ];
    }

    /**
     * @return array
     */
    public function useChangeSessionIdCallbackValues()
    {
        return [
            false,
            true
        ];
    }

    /**
     * @return array
     */
    public function loggerValues()
    {
        return [
            null,
            new SimpleLogger()
        ];
    }

    /**
     * @return array
     */
    public function cartesianProvider()
    {
        $params = [];
        foreach($this->configValues() as $config) {
            foreach($this->useChangeSessionIdCallbackValues() as $idCallback) {
                foreach($this->useGcCallbackValues() as $gcCallback) {
                    foreach($this->loggerValues() as $logger) {
                        $params[] = [
                            $config,
                            $idCallback,
                            $gcCallback,
                            $logger
                        ];
                    }
                }
            }
        }
        return $params;
    }

    /**
     * Emulate the presence of $_SERVER variables used for fingerprinting for
     * all test cases.
     *
     * Instantiate UltimateSessionHandler to guaranteee appropriate session
     * INI settings on test environment.
     */
    protected function setUp()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5)' .
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8';
        new UltimateSessionHandler(UltimateSessionHandlerConfig::getInstance());
    }

    protected function tearDown()
    {
        unset($_SESSION);
        unset($_COOKIE);
        unset($this->manager);
    }

    /**
     * Helper method for retrieving UltimateSessionManagerMetadata as stored
     * in $_SESSION.
     *
     * @return UltimateSessionManagerMetadata
     */
    protected function getMetadataFromSession() {
        return $_SESSION[UltimateSessionManager::METADATA_KEY];
    }

    /**
     * Helper method to allow for direct setting of metadata values to
     * $_SESSION in order to simulate different metadata states.
     *
     * @param mixed $metadata
     */
    protected function setMetadataToSession($metadata) {
        $_SESSION[UltimateSessionManager::METADATA_KEY] = $metadata;
    }

    /**
     * Convenience method used to instantiate and start session used commonly
     * across tests.
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean|null $useChangeIdCallback
     * @param boolean|null $useGcCallback
     * @param LoggerInterface|null $logger
     */
    protected function instantiateAndStartSession(
        UltimateSessionManagerConfig $config,
        $useChangeIdCallback = null,
        $useGcCallback = null,
        LoggerInterface $logger = null
    ) {
        $changeIdCallback = null;
        if($useChangeIdCallback === true) {
            $changeIdCallback = function ($oldSessionId, $newSessionId) {
                echo $oldSessionId . ':' . $newSessionId;
            };
        }
        $gcCallback = null;
        if($useGcCallback === true) {
            $gcCallback = function ($sessionId, $expiryTimestamp) {
                echo $sessionId;
            };
        }
        $this->manager = new UltimateSessionManager(
            $config,
            $changeIdCallback,
            $gcCallback,
            $logger
        );
        $this->manager->startSession();
        $_COOKIE[$this->manager->getSessionName()] = $this->manager->getSessionId();
    }

    protected function closeAndUnsetSession()
    {
        $this->manager->commitSession();
        unset($_SESSION);
    }


    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager::__construct()
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager::setLogger()
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testConstructorAndSetLogger(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $changeIdCallback = null;
        if($useChangeIdCallback === true) {
            $changeIdCallback = function ($oldSessionId, $newSessionId) {
                echo $oldSessionId . ':' . $newSessionId;
            };
        }
        $gcCallback = null;
        if($useGcCallback === true) {
            $gcCallback = function ($sessionId, $expiryTimestamp) {
                echo $sessionId;
            };
        }
        $sessionManager = new UltimateSessionManager(
            $config,
            $changeIdCallback,
            $gcCallback,
            $logger
        );
        $this->assertInstanceOf(
            UltimateSessionManager::class,
            $sessionManager
        );
        $logger = new SimpleLogger();
        $sessionManager->setLogger($logger);
        $this->assertAttributeEquals($logger, 'logger', $sessionManager);
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @expectedException \RuntimeException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager::startSession()
     */
    public function testStartSessionThrowsOnSessionStartFailure()
    {
        /**
         * Mocking session_start() for this test in order to force false result.
         *
         * @return false
         */
        function session_start() { return false; }
        $this->instantiateAndStartSession(new UltimateSessionManagerConfig());
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testSessionInitAndContinue(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        /**
         * Starting session fresh session which gets new session ID.
         */
        $metadata = $this->getMetadataFromSession();
        $this->assertAttributeEquals(
            $metadata,
            'metadata',
            $this->manager
        );
        $this->assertEquals(1, $metadata->sessionStartCount);
        $sessionId = $this->manager->getSessionId();
        $_SESSION['test'] = true;
        $this->closeAndUnsetSession();
        $this->manager->startSession();
        $metadata = $this->getMetadataFromSession();
        $this->assertAttributeEquals(
            $metadata,
            'metadata',
            $this->manager
        );
        $this->assertEquals(2, $metadata->sessionStartCount);
        $this->assertEquals($sessionId, $this->manager->getSessionId());
        $this->assertTrue($_SESSION['test']);
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @expectedException \RuntimeException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testMissingMetadataCausesException(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        $this->setMetadataToSession('');
        $this->closeAndUnsetSession();
        $this->manager->startSession();
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testInvalidSessionFingerprint(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        /**
         * Unset $_SERVER fingerprint variable for 25% of test cases to
         * enable test of 'NO FINGERPRINT AVAILABLE' code path, which does
         * not effect other aspects of this test other than initial
         * fingerprint creation.
         */
        if($useGcCallback === true && $logger instanceof SimpleLogger) {
            unset(
                $_SERVER['HTTP_USER_AGENT'],
                $_SERVER['HTTP_ACCEPT_ENCODING'],
                $_SERVER['HTTP_ACCEPT_LANGUAGE']
            );
        }
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        $sessionId = $this->manager->getSessionId();
        $metadata = $this->getMetadataFromSession();
        $goodFingerprint = $metadata->fingerprint;
        $badFingerprint = hash('sha256', 'Bad Fingerprint');
        $metadata->fingerprint = $badFingerprint;
        $this->setMetadataToSession($metadata);
        $this->closeAndUnsetSession();
        /**
         * This string portion from isFingerprintValid()
         */
        $logMessage = '';
        if ($logger instanceof SimpleLogger) {
            $logMessage .= LogLevel::NOTICE . ':';
        }
        $logMessage .= "Fingerprint mismatch.\nFingerprint in session: '" .
            $badFingerprint . "'\nFingerprint from request: '" .
            $goodFingerprint . "'\n" . ":0\n";
        /**
         * This string portion from startSession() via log()
         */
        if ($logger instanceof SimpleLogger) {
            $logMessage .= LogLevel::NOTICE . ':';
        }
        $logMessage .= "Session ID '" . $sessionId .
            "' invalidated. JSON metadata dump:\n" .
            json_encode($metadata, JSON_PRETTY_PRINT) . "\n" . ":0\n";
        if($useGcCallback === true) {
            $logMessage .= $sessionId;
        }
        $this->expectOutputString($logMessage);
        $result = $this->manager->startSession();
        $this->assertFalse($result);
        $this->assertEquals([], $_SESSION);
        $this->assertEquals('', $this->manager->getSessionId());
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testSessionForwarding(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        $metadata = $this->getMetadataFromSession();
        $metadata->isActive = false;
        $metadata->expireDataAt = time() + $config->ttlAfterIdRegen;
        $forwardedSessionId = substr_replace(
            $this->manager->getSessionId(),
            'aaaaaaaaaa',
            -10
        );
        $metadata->forwardToSessionId = $forwardedSessionId;
        $this->setMetadataToSession($metadata);
        $this->closeAndUnsetSession();
        $this->manager->startSession();
        $this->assertEquals($forwardedSessionId, $this->manager->getSessionId());
        $newMetadata = $this->getMetadataFromSession();
        $this->assertEquals(1, $newMetadata->sessionStartCount);
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testManualSessionIdRegeneration(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        $_SESSION['test'] = true;
        $initialSessionId = $this->manager->getSessionId();
        $this->manager->regenerateId();
        $newMetadata = $this->getMetadataFromSession();
        $newSessionId = $this->manager->getSessionId();
        $this->assertNotEquals($initialSessionId, $newSessionId);
        $this->assertEquals(1, $newMetadata->sessionStartCount);
        $this->assertGreaterThanOrEqual(
            time() - 1,
            $newMetadata->regenerateIdAt
        );
        $this->assertTrue($_SESSION['test']);
        $outputString = '';
        if($useChangeIdCallback) {
            $outputString .= $initialSessionId . ':' . $newSessionId;
        }
        if($useGcCallback) {
            $outputString .= $initialSessionId;
        }
        if(!empty($outputString)) {
            $this->expectOutputString($outputString);
        }
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManager
     *
     * @param UltimateSessionManagerConfig $config
     * @param boolean $useChangeIdCallback
     * @param boolean $useGcCallback
     * @param LoggerInterface|null $logger
     */
    public function testAutoSessionIdRegeneration(
        $config,
        $useChangeIdCallback,
        $useGcCallback,
        $logger
    ) {
        $expectRegen = true;
        if($config->regenIdCount === 0 && $config->regenIdInterval === 0) {
            $expectRegen = false;
        }
        $this->instantiateAndStartSession(
            $config,
            $useChangeIdCallback,
            $useGcCallback,
            $logger
        );
        $_SESSION['test'] = true;
        $initialSessionId = $this->manager->getSessionId();
        $metadata = $this->getMetadataFromSession();
        $metadata->sessionStartCount = 10;
        $metadata->regenerateIdAt = time() - 1;
        $this->setMetadataToSession($metadata);
        $this->closeAndUnsetSession();
        $this->manager->startSession();
        $newMetadata = $this->getMetadataFromSession();
        $newSessionId = $this->manager->getSessionId();
        if(!$expectRegen) {
            $this->assertEquals($initialSessionId, $newSessionId);
            $this->assertEquals(11, $newMetadata->sessionStartCount);
            $this->assertLessThanOrEqual(time(), $newMetadata->regenerateIdAt);
        } else {
            $this->assertNotEquals($initialSessionId, $newSessionId);
            $this->assertEquals(1, $newMetadata->sessionStartCount);
            $this->assertGreaterThanOrEqual(
                time() - 1,
                $newMetadata->regenerateIdAt
            );
        }
        $this->assertTrue($_SESSION['test']);
        $outputString = '';
        if($useChangeIdCallback && $expectRegen) {
            $outputString .= $initialSessionId . ':' . $newSessionId;
        }
        if($useGcCallback && $expectRegen) {
            $outputString .= $initialSessionId;
        }
        if(!empty($outputString)) {
            $this->expectOutputString($outputString);
        }
    }
}
