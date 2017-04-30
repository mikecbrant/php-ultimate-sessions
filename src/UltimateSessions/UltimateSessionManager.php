<?php

namespace MikeBrant\UltimateSessions;

class UltimateSessionManager
{
    /**
     * Setting to determine how many seconds after session id regeneration that
     * data is to remain available for read by proper validated session.  This
     * is to mitigate race conditions due to concurrent requests. Session
     * must still be considered invalid during this time period.
     *
     * @var int Number of seconds to retain session data after session id
     * regeneration.
     */
    protected $dataDestructionDelaySecs = 10;
}