<?php

namespace MikeBrant\UltimateSessions;

use PHPUnit\Framework\TestCase;

/**
 * Class UltimateSessionMetadataConfigTest
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionManagerMetadataTest extends TestCase
{
    /**
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerMetadata
     */
    public function testConstructor()
    {
        $metadata = new UltimateSessionManagerMetadata();
        $this->assertInternalType('integer', $metadata->instantiatedAt);
        $this->assertLessThanOrEqual(time(), $metadata->instantiatedAt);
        $this->assertEquals(0, $metadata->regenerateIdAt);
        $this->assertEquals(1, $metadata->sessionStartCount);
        $this->assertTrue($metadata->isActive);
        $this->assertEquals(0, $metadata->expireDataAt);
        $this->assertEquals('', $metadata->forwardToSessionId);
        $this->assertEquals('', $metadata->fingerprint);
    }

    /**
     * @covers \MikeBrant\UltimateSessions\UltimateSessionManagerMetadata::jsonSerialize()
     */
    public function testJsonSerialize()
    {
        $metadata = new UltimateSessionManagerMetadata();
        $properties = get_object_vars($metadata);
        $propertiesViaJson = json_decode(json_encode($metadata), true);
        $this->assertEquals($properties, $propertiesViaJson);
    }
}