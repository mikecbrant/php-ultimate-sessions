<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;
use Defuse\Crypto\Key;

/**
 * Simple wrapper class which does nothing but use trait under test.
 *
 * @package MikeBrant\UltimateSessions
 */
class TraitWrapperClass extends \SessionHandler implements UltimateSessionHandlerInterface
{
    use UltimateSessionHandlerTrait;

    public function __construct(UltimateSessionHandlerConfig $config)
    {
        $this->config = $config;
    }
}

class UltimateSessionHandlerTraitTest extends TestCase
{
    public static $validSessionId;

    public static $invalidSessionId;

    public static $sessionIdPhp7_1_0 =
        '123456789012345678901234567890123456789012345678';

    public static $sessionIdPhpBelow7_1_0 =
        '1234567890123456789012345678901234567890123456789012345678901234';

    /**
     * @var TraitWrapperClass
     */
    protected $trait;

    public static function setUpBeforeClass()
    {
        self::$validSessionId = self::$sessionIdPhpBelow7_1_0;
        self::$invalidSessionId = self::$sessionIdPhp7_1_0;
        if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
            self::$validSessionId = self::$sessionIdPhp7_1_0;
            self::$invalidSessionId = self::$sessionIdPhpBelow7_1_0;
        }
    }

    protected function setUp()
    {
        $config = UltimateSessionHandlerConfig::getInstance(false, 'x');
        $this->trait = new TraitWrapperClass($config);
    }

    protected function tearDown()
    {
        unset($this->trait);
    }

    public function sessionHandlerInitProvider()
    {
        return [
            [true, 'TESTKEY'],
            [false, 'TESTKEY']
        ];
    }

    public function validSessionIdProvider()
    {
        return [
            ['5.6.0', self::$sessionIdPhpBelow7_1_0],
            ['7.1.0', self::$sessionIdPhp7_1_0]
        ];
    }

    public function invalidSessionIdProvider()
    {
        return [
            ['5.6.0', ''],
            ['5.6.0', '0123456789abcdef'],
            ['7.1.0', ''],
            ['7.1.0', '0123456789abcdefghijklmnopqrtstuvwxyzAZ-']
        ];
    }

    public function invalidAsciiKeyProvider()
    {
        return [
            [null],
            [0],
            [1],
            [false],
            [true],
            [[]],
            ['']
        ];
    }

    public function encryptionDataProvider()
    {
        $stdClass = new \stdClass();
        $stdClass->key = 'value';
        $stdClass2 = new \stdClass();
        return [
            [$stdClass],
            [$stdClass2],
            [[1, 2, 3, 4]],
            [[]],
            ['string'],
            [''],
            [true],
            [false],
            [1],
            [0]
        ];
    }

    /**
     * This test requires process-isolation as we are defining a constant
     * constant here.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider sessionHandlerInitProvider();
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait
     */
    public function testSessionHandlerInit()
    {
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
        $this->trait->sessionHandlerInit();
        foreach($requiredIniSettings as $setting => $value) {
            $this->assertEquals($value, ini_get($setting));
        }
    }

    /**
     * This test requires process-isolation as we are defining a global
     * constant here. Here we set PHP Version to bad version for test
     * environment in order to trigger ini_set failure.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @expectedException \Exception
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait
     */
    public function testSessionHandlerInitThrowsExceptionOnUnableToSetIni()
    {
        /** Set to bad version */
        if (version_compare(PHP_VERSION, '7.1.0', '>=')) {
            define(__NAMESPACE__ . '\PHP_VERSION', '5.6.0');
        } else {
            define(__NAMESPACE__ . '\PHP_VERSION', '7.1.0');
        }
        $this->trait->sessionHandlerInit();
    }

    /**
     * @expectedException \Exception
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::sessionHandlerInit()
     */
    public function testSessionHandlerInitThrowsExceptionOnBadConfig()
    {
        $config = UltimateSessionHandlerConfig::getInstance('bad value', false);
        $this->trait = new TraitWrapperClass($config);
        $this->trait->sessionHandlerInit();
    }

    /**
     * This test requires process-isolation as we are defining a global
     * constant here.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider validSessionIdProvider()
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::validateSessionId()
     *
     * @param $phpVersion
     * @param $sessionId
     */
    public function testValidateSessionId($phpVersion, $sessionId)
    {
        define(__NAMESPACE__ . '\PHP_VERSION', $phpVersion);
        $this->trait->validateSessionId($sessionId);
        /** dummy assertion */
        $this->assertTrue(true);
    }

    /**
     * This test requires process-isolation as we are defining a constant
     * constant here.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @expectedException \InvalidArgumentException
     * @dataProvider invalidSessionIdProvider()
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::validateSessionId()
     *
     * @param $phpVersion
     * @param $sessionId
     */
    public function testValidateSessionIdThrowsException($phpVersion, $sessionId)
    {
        define(__NAMESPACE__ . '\PHP_VERSION', $phpVersion);
        $this->trait->validateSessionId($sessionId);
    }

    /**
     * @dataProvider invalidAsciiKeyProvider
     * @expectedException \InvalidArgumentException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::setEncryptionKeyCookie()
     * @param $asciiKey
     */
    public function testSetEncryptionKeyCookieThrowsExceptions($asciiKey)
    {
        $this->trait->setEncryptionKeyCookie(self::$validSessionId, $asciiKey);
    }

    /**
     * This test requires process-isolation as we are "setting" cookies in
     * code under test and will get headers already sent warning from PhpUnit
     * test runner output if we don't.
     *
     * @runInSeparateProcess
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::deleteEncryptionKeyCookie()
     */
    public function testDeleteEncryptionKeyCookie()
    {
        $cookiePrefix = 'ULTSESS_';
        $cookieKey = $cookiePrefix . self::$validSessionId;
        $headerValue = 'Set-Cookie: ' . $cookieKey . '=';
        $config = UltimateSessionHandlerConfig::getInstance(true, $cookiePrefix);
        $this->trait = new TraitWrapperClass($config);
        $this->trait->sessionHandlerInit();
        $key = $this->trait->getEncryptionKey(self::$validSessionId);
        $this->trait->deleteEncryptionKeyCookie(self::$validSessionId);
        $this->assertArrayNotHasKey($cookieKey, $_COOKIE);
        /**
         * Initial cookie set
         */
        $this->assertStringStartsWith(
            $headerValue . $key->saveToAsciiSafeString(),
            xdebug_get_headers()[0]
        );
        /**
         * Cookie delete
         */
        $this->assertStringStartsWith(
            $headerValue . 'deleted',
            xdebug_get_headers()[1]
        );
    }

    /**
     * his test requires process-isolation as we are "setting" cookies in
     * code under test and will get headers already sent warning from PhpUnit
     * test runner output if we don't.
     *
     * @runInSeparateProcess
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait::changeKeyCookieSessionId()
     */
    public function testChangeKeyCookieSessionId()
    {
        $initialSessionId = self::$validSessionId;
        $newSessionId = str_replace('1', 'a', $initialSessionId);
        $cookiePrefix = 'ULTSESS_';
        $initialCookieKey = $cookiePrefix . $initialSessionId;
        $newCookieKey = $cookiePrefix . $newSessionId;
        $deletedCookieHeader = 'Set-Cookie: ' . $initialCookieKey . '=';
        $newCookieHeader = 'Set-Cookie: ' . $newCookieKey . '=';
        $config = UltimateSessionHandlerConfig::getInstance(true, $cookiePrefix);
        $this->trait = new TraitWrapperClass($config);
        $this->trait->sessionHandlerInit();
        $key = $this->trait->getEncryptionKey($initialSessionId);
        $this->assertArrayHasKey($initialCookieKey, $_COOKIE);
        $asciiKey = $_COOKIE[$initialCookieKey];
        $this->trait->changeKeyCookieSessionId($initialSessionId, $newSessionId);
        $this->assertArrayNotHasKey($initialCookieKey, $_COOKIE);
        $this->assertArrayHasKey($newCookieKey, $_COOKIE);
        $this->assertEquals($asciiKey, $_COOKIE[$newCookieKey]);
        $this->assertEquals($key, $this->trait->getEncryptionKey($newSessionId));
        /**
         * Initial cookie set
         */
        $this->assertStringStartsWith(
            $deletedCookieHeader . $asciiKey,
            xdebug_get_headers()[0]
        );
        /**
         * Cookie delete
         */
        $this->assertStringStartsWith(
            $deletedCookieHeader . 'deleted',
            xdebug_get_headers()[1]
        );
        /**
         * New cookie set
         */
        $this->assertStringStartsWith(
            $newCookieHeader. $asciiKey,
            xdebug_get_headers()[2]
        );
    }

    /**
     * This test requires process-isolation as we are "setting" cookies in
     * code under test and will get headers already sent warning from PhpUnit
     * test runner output if we don't.
     *
     * @runInSeparateProcess
     * @dataProvider encryptionDataProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandlerTrait
     */
    public function testEncryptAndDecrypt($testData)
    {
        $serializedData = serialize($testData);
        $cookiePrefix = 'ULTSESS_';
        $cookieKey = $cookiePrefix . self::$validSessionId;
        $config = UltimateSessionHandlerConfig::getInstance(true, $cookiePrefix);
        $this->trait = new TraitWrapperClass($config);
        $this->trait->sessionHandlerInit();
        $encryptedData = $this->trait->encrypt(
            self::$validSessionId,
            $serializedData
        );
        /** retrieve cookie via set* to recover from $_COOKIE */
        $key = $this->trait->setEncryptionKey(self::$validSessionId);
        $this->assertInstanceOf(Key::class, $key);
        $asciiKey = $key->saveToAsciiSafeString();
        $this->assertEquals($asciiKey, $_COOKIE[$cookieKey]);
        $headerValue = 'Set-Cookie: ' . $cookieKey . '=' . $asciiKey;
        $this->assertStringStartsWith($headerValue, xdebug_get_headers()[0]);

        $decryptedData = $this->trait->decrypt(
            self::$validSessionId,
            $encryptedData
        );
        $this->assertEquals($serializedData, $decryptedData);
    }
}