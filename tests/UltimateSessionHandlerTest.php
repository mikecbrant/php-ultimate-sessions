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
    public function instantiationProvider()
    {
        return [
            [null, null],
            [false, 'PREFIX_'],
            [false, null],
            [true, 'PREFIX_'],
            [true, null]
        ];
    }

    public function dataProvider()
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

    public function cartesianProvider()
    {
        $instantiations = $this->instantiationProvider();
        $data = $this->dataProvider();
        $cartesian = [];
        foreach($data as $row) {
            foreach($instantiations as $instantiation) {
                $cartesian[] = array_merge($instantiation, $row);
            }
        }

        return $cartesian;
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider cartesianProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionHandler
     */
    public function testReadAndWrite($useEncryption, $encryptionCookiePrefix, $data)
    {
        if (is_null($useEncryption)) {
            $handler = new UltimateSessionHandler();
        } elseif (is_null($encryptionCookiePrefix)) {
            $handler = new UltimateSessionHandler($useEncryption);
        } else {
            $handler = new UltimateSessionHandler(
                $useEncryption,
                $encryptionCookiePrefix
            );
        }
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