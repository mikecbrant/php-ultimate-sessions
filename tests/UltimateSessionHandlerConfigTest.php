<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionHandlerConfigTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionHandlerConfigTest extends TestCase
{
    /**
     * @return array
     */
    public function getInstanceProvider()
    {
        return [
            [],
            [true, 'PREFIX_'],
            [false]
        ];
    }

    /**
     * @return array
     */
    public function validationExceptionProvider()
    {
        return [
            /** invalid string set on cookieDomain */
            [[null, 0, '/', true, true, 'prefix']],
            [[false, 0, '/', true, true, 'prefix']],
            [[1, 0, '/', true, true, 'prefix']],
            [[0, 0, '/', true, true, 'prefix']],
            /** invalid non-negative int set on cookieLifetime */
            [['domain', -1, '/', true, true, 'prefix']],
            [['domain', '', '/', true, true, 'prefix']],
            [['domain', '0', '/', true, true, 'prefix']],
            [['domain', null, '/', true, true, 'prefix']],
            [['domain', false, '/', true, true, 'prefix']],
            /** invalid string set on cookiePath */
            [['domain', 0, null, true, true, 'prefix']],
            [['domain', 0, false, true, true, 'prefix']],
            [['domain', 0, 1, true, true, 'prefix']],
            [['domain', 0, 0, true, true, 'prefix']],
            /** invalid boolean set on cookieSecure */
            [['domain', 0, '/', 1, true, 'prefix']],
            [['domain', 0, '/', 0, true, 'prefix']],
            [['domain', 0, '/', 'true', true, 'prefix']],
            [['domain', 0, '/', '', true, 'prefix']],
            /** invalid boolean set on useEncryption */
            [['domain', 0, '/', true, 1, 'prefix']],
            [['domain', 0, '/', true, 0, 'prefix']],
            [['domain', 0, '/', true,'true', 'prefix']],
            [['domain', 0, '/', true, '', 'prefix']],
            /** invalid non-empty string set on keyCookiePrefix */
            [['domain', 0, '/', true, true, '']],
            [['domain', 0, '/', true, true, null]],
            [['domain', 0, '/', true, true, false]],
            [['domain', 0, '/', true, true, 1]],
            [['domain', 0, '/', true, true, 0]],
        ];
    }

    /**
     * @dataProvider getInstanceProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerConfig
     *
     * @param boolean $useEncryption
     * @param string $keyCookiePrefix
     * @throws \InvalidArgumentException
     */
    public function testGetInstanceAndConstructorTypicalUsePaths(
        $useEncryption = false,
        $keyCookiePrefix = UltimateSessionHandlerConfig::DEFAULT_KEY_COOKIE_PREFIX
    ) {
        $config = UltimateSessionHandlerConfig::getInstance($useEncryption, $keyCookiePrefix);
        $this->assertEquals(
            ini_get('session.cookie_domain'),
            $config->cookieDomain
        );
        $this->assertEquals(
            (int)ini_get('session.cookie_lifetime'),
            $config->cookieLifetime
        );
        $this->assertEquals(
            ini_get('session.cookie_path'),
            $config->cookiePath
        );
        $this->assertEquals(
            (bool)ini_get('session.cookie_secure'),
            $config->cookieSecure
        );
        $this->assertEquals($useEncryption, $config->useEncryption);
        $this->assertEquals(
            $keyCookiePrefix,
            $config->keyCookiePrefix
        );
    }

    /**
     * @expectedException \OutOfBoundsException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::__get()
     */
    public function testGetMagicMethodThrowsException()
    {
        $config = new UltimateSessionHandlerConfig();
        $config->badProperty;
    }

    /**
     * @dataProvider validationExceptionProvider
     * @expectedException \InvalidArgumentException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::<protected>
     *
     * @param array $valueArray
     */
    public function testValidatorsThrowExceptions($valueArray)
    {
        $config = [];
        $config['cookieDomain'] = $valueArray[0];
        $config['cookieLifetime'] = $valueArray[1];
        $config['cookiePath'] = $valueArray[2];
        $config['cookieSecure'] = $valueArray[3];
        $config['useEncryption'] = $valueArray[4];
        $config['keyCookiePrefix'] = $valueArray[5];

        new UltimateSessionHandlerConfig($config);
    }
}