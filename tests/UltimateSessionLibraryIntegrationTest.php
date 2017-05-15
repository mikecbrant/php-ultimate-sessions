<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionLibraryIntegrationTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionLibraryIntegrationTest extends TestCase
{
    /**
     * @return UltimateSessionHandlerConfig[]
     */
    public function handlerConfig() {
        return [
            UltimateSessionHandlerConfig::getInstance(false),
            UltimateSessionHandlerConfig::getInstance(true)
        ];
    }

    /**
     * @return UltimateSessionManagerConfig[]
     */
    public function managerConfig() {
        return [
            new UltimateSessionManagerConfig(),
            new UltimateSessionManagerConfig(0, 0),
            new UltimateSessionManagerConfig(0),
            new UltimateSessionManagerConfig(null, 0)
        ];
    }

    /**
     * @return array
     */
    public function sessionConfigProvider() {
        $params = [];
        foreach ($this->handlerConfig() as $handlerConfig) {
            foreach($this->managerConfig() as $managerConfig) {
                $params[] = [$handlerConfig, $managerConfig];
            }
        }
        return $params;
    }

    /**
     * Emulate the presence of $_SERVER variables used for fingerprinting for
     * all test cases.
     */
    protected function setUp()
    {
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5)' .
            'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/56.0.2924.87 Safari/537.36';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, sdch';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8';
    }

    /**
     * Helper function to extract set cookie headers
     *
     * @return array
     */
    protected function getCoookieHeaders() {
        $headers = xdebug_get_headers();
        $return = [];
        foreach($headers as $header) {
            if(strpos($header, 'Set-Cookie: ') === 0) {
                $return[] = $header;
            }
        }
        return $return;
    }

    /**
     * Helper function to extract cookie headers into associative array that
     * can be used to set $_COOKIE.
     *
     * @return array
     */
    protected function getCookieArray() {
        $cookieHeaders = $this->getCoookieHeaders();
        $cookies = [];
        foreach($cookieHeaders as $header) {
            $header = str_replace('Set-Cookie: ', '', $header);
            $stringParts = explode(';', $header);
            $kvPair = explode('=', $stringParts[0]);
            $cookies[$kvPair[0]] = $kvPair[1];
        }
        return $cookies;
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider sessionConfigProvider
     * @coversNothing
     * @outputBuffering disabled
     *
     * @param UltimateSessionHandlerConfig $handlerConfig
     * @param UltimateSessionManagerConfig $managerConfig
     */
    public function testSessionPersistence(
        UltimateSessionHandlerConfig $handlerConfig,
        UltimateSessionManagerConfig $managerConfig
    ) {
        $encryptedSession = $handlerConfig->useEncryption;
        if($encryptedSession) {
            $keyCookiePrefix = $handlerConfig->keyCookiePrefix;
        }

        $handler = new UltimateSessionHandler($handlerConfig);
        $changeIdCallback = function ($oldSessionId, $newSessionId) use ($handler)
        {
            $handler->changeKeyCookieSessionId($oldSessionId, $newSessionId);
        };
        if($encryptedSession) {
            $manager = new UltimateSessionManager(
                $managerConfig,
                $changeIdCallback
            );
        } else {
            $manager = new UltimateSessionManager($managerConfig);
        }
        $sessionName = $manager->getSessionName();
        /**
         * Start and end session
         */
        $manager->startSession();
        $_SESSION['test'] = true;
        $sessionId = $manager->getSessionId();
        $manager->commitSession();
        $cookieHeaders = $this->getCoookieHeaders();
        /**
         * Session Cookie
         */
        $this->assertStringStartsWith(
            'Set-Cookie: ' . $sessionName . '=' . $sessionId,
            $cookieHeaders[0]
        );
        /**
         * Encryption key cookie
         */
        if($encryptedSession) {
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $sessionId,
                $cookieHeaders[1]
            );
        }
        $_COOKIE = $this->getCookieArray();
        unset($manager);
        $_SESSION = [];
        header_remove();


        /**
         * Simulate new request
         */
        if($encryptedSession) {
            $manager = new UltimateSessionManager(
                $managerConfig,
                $changeIdCallback
            );
        } else {
            $manager = new UltimateSessionManager($managerConfig);
        }
        $manager->startSession();
        $this->assertEquals($sessionId, $manager->getSessionId());
        $this->assertTrue($_SESSION['test']);
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider sessionConfigProvider
     * @coversNothing
     * @outputBuffering disabled
     *
     * @param UltimateSessionHandlerConfig $handlerConfig
     * @param UltimateSessionManagerConfig $managerConfig
     */
    public function testSessionRegenerationAndForwarding(
        UltimateSessionHandlerConfig $handlerConfig,
        UltimateSessionManagerConfig $managerConfig
    )
    {
        $encryptedSession = $handlerConfig->useEncryption;
        if($encryptedSession) {
            $keyCookiePrefix = $handlerConfig->keyCookiePrefix;
        }

        $handler = new UltimateSessionHandler($handlerConfig);
        $changeIdCallback = function ($oldSessionId, $newSessionId) use ($handler)
        {
            $handler->changeKeyCookieSessionId($oldSessionId, $newSessionId);
        };
        if($encryptedSession) {
            $manager = new UltimateSessionManager(
                $managerConfig,
                $changeIdCallback
            );
        } else {
            $manager = new UltimateSessionManager($managerConfig);
        }
        $sessionName = $manager->getSessionName();
        /**
         * Start session
         */
        $manager->startSession();
        $_SESSION['test'] = true;
        $sessionId = $manager->getSessionId();
        $manager->regenerateId();
        $newSessionId = $manager->getSessionId();
        $this->assertNotEquals($sessionId, $newSessionId);
        $_SESSION['test2'] = true;
        $manager->commitSession();
        $cookieHeaders = $this->getCoookieHeaders();
        $cookieCounter = 0;
        /**
         * Initial Session Cookie
         */
        $this->assertStringStartsWith(
            'Set-Cookie: ' . $sessionName . '=' . $sessionId,
            $cookieHeaders[$cookieCounter]
        );
        $cookieCounter++;
        /**
         * Encryption key cookie
         */
        if($encryptedSession) {
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $sessionId,
                $cookieHeaders[$cookieCounter]
            );
            $cookieCounter++;
        }
        /**
         * New session cookie
         */
        $this->assertStringStartsWith(
            'Set-Cookie: ' . $sessionName . '=' . $newSessionId,
            $cookieHeaders[$cookieCounter]
        );
        $cookieCounter++;
        /**
         * Old encryption cookie is deleted and new one sent
         */
        if($encryptedSession) {
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $sessionId . '=deleted',
                $cookieHeaders[$cookieCounter]
            );
            $cookieCounter++;
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $newSessionId,
                $cookieHeaders[$cookieCounter]
            );
        }

        $expectedCookies = $this->getCookieArray();
        unset($manager);
        $_SESSION = [];
        header_remove();

        /**
         * Start new session under old session ID and with old cookies. We
         * don't expect cookie value for PHP session id to be reflected until
         * subsequent request.
         */
        $_COOKIE[$sessionName] = $sessionId;
        $expectedCookies[$sessionName] = $newSessionId;
        session_id($sessionId);
        if($encryptedSession) {
            /**
             * Get encryption key from new session id key and set to old
             */
            $_COOKIE[$keyCookiePrefix . $sessionId] =
                $expectedCookies[$keyCookiePrefix . $newSessionId];
            unset($_COOKIE[$keyCookiePrefix . $newSessionId]);
            /**
             * Remove old session id key from expected array.
             */
            unset($expectedCookies[$keyCookiePrefix . $sessionId]);
        }
        if($encryptedSession) {
            $manager = new UltimateSessionManager(
                $managerConfig,
                $changeIdCallback
            );
        } else {
            $manager = new UltimateSessionManager($managerConfig);
        }
        $manager->startSession();
        $this->assertEquals($newSessionId, $manager->getSessionId());
        $this->assertTrue($_SESSION['test']);
        $this->assertTrue($_SESSION['test2']);
        $cookieHeaders = $this->getCoookieHeaders();
        /**
         * Old session cookie
         */
        $this->assertStringStartsWith(
            'Set-Cookie: ' . $sessionName . '=' . $sessionId,
            $cookieHeaders[0]
        );
        /**
         * New session cookie
         */
        $this->assertStringStartsWith(
            'Set-Cookie: ' . $sessionName . '=' . $newSessionId,
            $cookieHeaders[1]
        );
        /**
         * Old encryption cookie is deleted and new one sent
         */
        if($encryptedSession) {
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $sessionId . '=deleted',
                $cookieHeaders[2]
            );
            $this->assertStringStartsWith(
                'Set-Cookie: ' . $keyCookiePrefix . $newSessionId,
                $cookieHeaders[3]
            );
            $this->assertEquals($expectedCookies, $_COOKIE);
        }
    }
}