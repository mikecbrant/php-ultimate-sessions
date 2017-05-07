<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionManagerConfigTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionManagerConfigTest extends TestCase
{
    /**
     * @return array
     */
    public function constructorProvider()
    {
        return [
            [null, null, null, null],
            ['PHPSESSID', null, null, null],
            [null, 0, 0, 0],
            [null, 100, 100, 100],
            ['PHPSESSID', 0, 0, 0],
            ['PHPSESSID', 100, 100, 100]
        ];
    }

    /**
     * @return array
     */
    public function validationExceptionProvider()
    {
        return [
            /** invalid string set on sessionName */
            [false, null, null, null],
            [1, null, null, null],
            [0, null, null, null],
            ['', null, null, null],
            /** invalid non-negative int set on regenIdInterval */
            [null, -1, null, null],
            [null, '', null, null],
            [null, '0', null, null],
            [null, false, null, null],
            /** invalid string set on regenIdCount */
            [null, null, -1, null],
            [null, null, '', null],
            [null, null, '0', null],
            [null, null, false, null],
            /** invalid boolean set on ttlAfterIdRegen */
            [null, null, null, -1],
            [null, null, null, ''],
            [null, null, null, '0'],
            [null, null, null, false]
        ];
    }

    /**
     * @dataProvider constructorProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerConfig
     *
     * @param string|null $sessionName
     * @param int|null $regenIdInterval
     * @param int|null $regenIdCount
     * @param int|null $ttlAfterIdRegen
     */
    public function testConstructorAndTypicalUsePaths(
        $sessionName,
        $regenIdInterval,
        $regenIdCount,
        $ttlAfterIdRegen
    ) {
        $config = new UltimateSessionManagerConfig(
            $sessionName,
            $regenIdInterval,
            $regenIdCount,
            $ttlAfterIdRegen
        );
        if(!is_null($sessionName)) {
            $this->assertEquals($sessionName, $config->sessionName);
        } else {
            $this->assertNull($config->sessionName);
        }
        if(!is_null($regenIdInterval)) {
            $this->assertEquals($regenIdInterval, $config->regenIdInterval);
        } else {
            $this->assertEquals(15, $config->regenIdInterval);
        }
        if(!is_null($regenIdCount)) {
            $this->assertEquals($regenIdCount, $config->regenIdCount);
        } else {
            $this->assertEquals(10, $config->regenIdCount);
        }
        if(!is_null($ttlAfterIdRegen)) {
            $this->assertEquals($ttlAfterIdRegen, $config->ttlAfterIdRegen);
        } else {
            $this->assertEquals(30, $config->ttlAfterIdRegen);
        }
    }

    /**
     * @expectedException \OutOfBoundsException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerConfig::__get()
     */
    public function testGetMagicMethodThrowsException()
    {
        $config = new UltimateSessionManagerConfig();
        $config->badProperty;
    }

    /**
     * @dataProvider validationExceptionProvider
     * @expectedException \InvalidArgumentException
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerConfig::<protected>
     *
     * @param string|null $sessionName
     * @param int|null $regenIdInterval
     * @param int|null $regenIdCount
     * @param int|null $ttlAfterIdRegen
     */
    public function testValidatorsThrowExceptions(
        $sessionName,
        $regenIdInterval,
        $regenIdCount,
        $ttlAfterIdRegen
    ) {
        new UltimateSessionManagerConfig(
            $sessionName,
            $regenIdInterval,
            $regenIdCount,
            $ttlAfterIdRegen
        );
    }
}