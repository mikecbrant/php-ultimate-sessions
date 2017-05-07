<?php

namespace MikeBrant\UltimateSessions;

/**
 * Class UltimateSessionManagerConfig
 *
 * Configuration object which is passed to UltimateSessionManager class as
 * dependency.  The default configuration values should suffice for most use
 * cases, so the following would be typical usage to instantiate configuration
 * into UltimateSessionManager:
 *
 * $session = new UltimateSessionManager(new UltimateSessionManagerConfig);
 *
 * @package MikeBrant\UltimateSessions
 * @property-read string sessionName
 * @property-read int regenIdInterval
 * @property-read int regenIdCount
 * @property-read int ttlAfterIdRegen
 */
class UltimateSessionManagerConfig
{
    /**
     * Value used for calls to PHP's session_name() function. If left empty,
     * PHP's default session name as configured for environment (often
     * PHPSESSID) will be used.
     *
     * @var string
     */
    protected $sessionName;

    /**
     * Value to determine time (in minutes) after which forced session
     * id regeneration will occur. Must be non-negative integer value. A
     * value of 0 indicates no time-based id rotation.
     *
     * @var int
     */
    protected $regenIdInterval = 15;

    /**
     * Value to determine the number of session_start events after which
     * forced session id regeneration will occur. Must be non-negative
     * integer value. A value of 0 indicates no forced id rotation.
     *
     * @var int
     */
    protected $regenIdCount = 10;

    /**
     * Value to determine how long (in seconds) past session
     * id regeneration event to no longer allow access to session data to
     * formerly valid session. Must be non-negative integer value.
     *
     * @var int
     */
    protected $ttlAfterIdRegen = 30;

    /**
     * UltimateSessionManagerConfig constructor. Suggested usage is to not
     * pass any arguments to constructor unless you have specific reason to
     * vary from default values.
     *
     * @param string $sessionName
     * @param int $regenIdInterval
     * @param int $regenIdCount
     * @param int $ttlAfterIdRegen
     */
    public function __construct(
        $sessionName = null,
        $regenIdInterval = null,
        $regenIdCount = null,
        $ttlAfterIdRegen = null
    ) {
        if(!is_null($sessionName)) {
            $this->setSessionName($sessionName);
        }
        if(!is_null($regenIdInterval)) {
            $this->setRegenIdInterval($regenIdInterval);
        }
        if(!is_null($regenIdCount)) {
            $this->setRegenIdCount($regenIdCount);
        }
        if(!is_null($ttlAfterIdRegen)) {
            $this->setTtlAfterIdRegen($ttlAfterIdRegen);
        }
    }

    /**
     * Magic getter to provide public-like access to config.
     *
     * @param string $name
     * @return mixed
     * @throws \OutOfBoundsException
     */
    public function __get($name)
    {
        if (!property_exists(UltimateSessionManagerConfig::class, $name)) {
            throw new \OutOfBoundsException(
                "The property '{$name}' you have tried to access does not exist."
            );
        }

        return $this->{$name};
    }

    /**
     * @param string $sessionName
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setSessionName($sessionName)
    {
        $this->validateNonEmptyString($sessionName);
        $this->sessionName = $sessionName;
    }

    /**
     * @param int $minutes
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setRegenIdInterval($minutes)
    {
        $this->validateNonNegativeInteger($minutes);
        $this->regenIdInterval = $minutes;
    }

    /**
     * @param int $count
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setRegenIdCount($count)
    {
        $this->validateNonNegativeInteger($count);
        $this->regenIdCount = $count;
    }

    /**
     * @param int $seconds
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setTtlAfterIdRegen($seconds)
    {
        $this->validateNonNegativeInteger($seconds);
        $this->ttlAfterIdRegen = $seconds;
    }

    /**
     * @param $integer
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateInteger($integer)
    {
        if (!is_int($integer)) {
            throw new \InvalidArgumentException('Value must be integer.');
        }
    }

    /**
     * @param $integer
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateNonNegativeInteger($integer)
    {
        $this->validateInteger($integer);
        if ($integer < 0) {
            throw new \InvalidArgumentException(
                'Value must be non-negative integer.'
            );
        }
    }

    /**
     * @param $string
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateString($string)
    {
        if (!is_string($string)) {
            throw new \InvalidArgumentException('Value must be string.');
        }
    }

    /**
     * @param $string
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateNonEmptyString($string)
    {
        $this->validateString($string);
        if (empty($string)) {
            throw new \InvalidArgumentException(
                'Value must be non-empty string.'
            );
        }
    }
}