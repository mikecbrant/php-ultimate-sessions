<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionHandlerTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionHandlerTest extends TestCase
{

    /**
     * @return array
     */
    public function useEncryptionDataOptions()
    {
        return [
            null,
            false,
            true
        ];

    }

    /**
     * @return array
     */
    public function keyCookiePrefixOptions()
    {
        return [
            null,
            'PREFIX_'
        ];
    }

    /**
     * @return array
     */
    public function dataOptions()
    {
        $stdClass = new \stdClass();
        $stdClass->key = 'value';
        $stdClass2 = new \stdClass();
        return [
            $stdClass,
            $stdClass2,
            [1, 2, 3, 4],
            [],
            'string',
            '',
            true,
            false,
            1,
            0
        ];
    }

    /**
     * @return array
     */
    public function cartesianProvider()
    {
        $params = [];
        foreach($this->useEncryptionDataOptions() as $encrypt) {
            foreach($this->keyCookiePrefixOptions() as $prefix) {
                foreach($this->dataOptions() as $data) {
                    $params[] = [$encrypt, $prefix, $data];
                }
            }
        }
        return $params;
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandler
     *
     * @param boolean|null $useEncryption
     * @param string|null $keyCookiePrefix
     * @param mixed $data
     */
    public function testReadAndWrite($useEncryption, $keyCookiePrefix, $data)
    {
        if (is_null($useEncryption)) {
            $config = UltimateSessionHandlerConfig::getInstance();
        } elseif (is_null($keyCookiePrefix)) {
            $config = UltimateSessionHandlerConfig::getInstance($useEncryption);
        } else {
            $config = UltimateSessionHandlerConfig::getInstance(
                $useEncryption,
                $keyCookiePrefix
            );
        }
        $handler = new UltimateSessionHandler($config);
        $this->assertInstanceOf(UltimateSessionHandler::class, $handler);
        session_start();
        $_SESSION['key'] = $data;
        session_write_close();
        unset($_SESSION);
        session_start();
        /** @noinspection PhpUndefinedVariableInspection */
        $this->assertEquals($data, $_SESSION['key']);
    }
}