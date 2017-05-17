<?php

namespace MikeBrant\UltimateSessions;

use Psr\Log\LoggerInterface;

/**
 * Class UltimateSessionLoader
 *
 * A simple static interface provided to allow for one-line instantiation of
 * the UltimateSession library using recommended settings, with a few minimal
 * configuration options.
 *
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionLoader
{
    /**
     * Single static method in this class provided to allow setup of
     * UltimateSession library using recommended settings along with a few
     * key configuration options.
     *
     * @param bool $useEncryption
     * @param callable|null $gcNotificationCallback
     * @param LoggerInterface|null $logger
     * @return UltimateSessionManager
     * @throws \InvalidArgumentException
     */
    public static function initialize(
        $useEncryption = false,
        LoggerInterface $logger = null
    ) {
        if(!is_bool($useEncryption)) {
            throw new \InvalidArgumentException('Value must be boolean.');
        }
        $handler = new UltimateSessionHandler(
            UltimateSessionHandlerConfig::getInstance($useEncryption)
        );
        $changeIdCallback = null;
        if($useEncryption) {
            $changeIdCallback = function ($oldSessionId, $newSessionId) use ($handler)
            {
                $handler->changeKeyCookieSessionId(
                    $oldSessionId,
                    $newSessionId
                );
            };
        }
        return new UltimateSessionManager(
            new UltimateSessionManagerConfig(),
            $changeIdCallback,
            null,
            $logger
        );
    }
}