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
            [null, null, null],
            [0, null, null],
            [100, null, null],
            [null, 0, null],
            [null, 100, null],
            [null, null, 0],
            [null, null, 100],
            [0, 0, 0],
            [100, 100, 100]
        ];
    }

    /**
     * @return array
     */
    public function validationExceptionProvider()
    {
        return [
            /** invalid non-negative int set on regenIdInterval */
            [-1, null, null],
            ['', null, null],
            ['0', null, null],
            [false, null, null],
            /** invalid string set on regenIdCount */
            [null, -1, null],
            [null, '', null],
            [null, '0', null],
            [null, false, null],
            /** invalid boolean set on ttlAfterIdRegen */
            [null, null, -1],
            [null, null, ''],
            [null, null, '0'],
            [null, null, false]
        ];
    }

    /**
     * @dataProvider constructorProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerConfig
     *
     * @param int|null $regenIdInterval
     * @param int|null $regenIdCount
     * @param int|null $ttlAfterIdRegen
     */
    public function testConstructorAndTypicalUsePaths(
        $regenIdInterval,
        $regenIdCount,
        $ttlAfterIdRegen
    ) {
        $config = new UltimateSessionManagerConfig(
            $regenIdInterval,
            $regenIdCount,
            $ttlAfterIdRegen
        );
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
     * @param int|null $regenIdInterval
     * @param int|null $regenIdCount
     * @param int|null $ttlAfterIdRegen
     */
    public function testValidatorsThrowExceptions(
        $regenIdInterval,
        $regenIdCount,
        $ttlAfterIdRegen
    ) {
        new UltimateSessionManagerConfig(
            $regenIdInterval,
            $regenIdCount,
            $ttlAfterIdRegen
        );
    }
}