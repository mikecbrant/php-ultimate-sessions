<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionLoaderTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionLoaderTest extends TestCase
{
    /**
     * @return array
     */
    public function useEncryptionProvider()
    {
        return [
            [false],
            [true]
        ];
    }

    /**
     * @return array
     */
    public function notBooleanProvider()
    {
        return [
            [''],
            ['true'],
            ['false'],
            ['1'],
            ['0'],
            [1],
            [0],
            [[]],
            [new \stdClass()]
        ];
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider useEncryptionProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionLoader
     * @outputBuffering disabled
     *
     * @param boolean $useEncryption
     */
    public function testInitializeAndRegenerateID($useEncryption)
    {
        $manager = UltimateSessionLoader::initialize($useEncryption);
        $this->assertInstanceOf(
            UltimateSessionManager::class,
            $manager
        );
        $manager->startSession();
        $sessionId = $manager->getSessionId();
        $this->assertNotEmpty($sessionId);
        $manager->regenerateId();
        $this->assertNotEquals($sessionId, $manager->getSessionId());
    }

    /**
     * This test requires process-isolation.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @dataProvider notBooleanProvider
     * @covers \MikeBrant\UltimateSessions\UltimateSessionLoader
     * @outputBuffering disabled
     * @expectedException \InvalidArgumentException
     *
     * @param boolean $useEncryption
     */
    public function testInitializeThrowsException($useEncryption)
    {
        UltimateSessionLoader::initialize($useEncryption);
    }
}
