<?php

namespace MikeBrant\UltimateSessions;

/**
 * Class UltimateSessionConfig
 *
 * This class stores session configuration values used by
 * UltimateSessionHandlerTrait. This class also provides input validation for
 * values being set into php.ini.
 *
 * @package MikeBrant\UltimateSessions
 * @property-read boolean useEncryption
 * @property-read string cookieDomain
 * @property-read int cookieLifetime
 * @property-read string cookiePath
 * @property-read boolean cookieSecure
 * @property-read string $keyCookiePrefix
 */
class UltimateSessionConfig
{
    /**
     * Class constant for default encryption key prefix value
     *
     * @var string Default encryption key prefix.
     */
    const DEFAULT_KEY_COOKIE_PREFIX = 'ULTSESSKEY_';

    /**
     * Flag determining if encryption is to be used for the session.
     *
     * @var bool Flag for encryption use.
     */
    protected $useEncryption = false;

    /**
     * Cookie domain to be used for encryption key cookie. This should match
     * domain used for session cookie.
     *
     * @var string Encryption key cookie domain.
     */
    protected $cookieDomain = '';

    /**
     * Cookie lifetime to be used for encryption key cookie. This should match
     * lifetime used for session cookie.
     *
     * @var int Encryption key cookie lifetime.
     */
    protected $cookieLifetime = 0;

    /**
     * Cookie path to be used for encryption key cookie. This should match
     * path used for session cookie.
     *
     * @var string Encryption key cookie path.
     */
    protected $cookiePath = '/';

    /**
     * Cookie security setting to be used for encryption key cookie. This
     * should match setting used for session cookie.
     *
     * @var bool Encryption key secure cookie flag.
     */
    protected $cookieSecure = false;

    /**
     * Prefix used for determining name used for encryption key cookie.
     *
     * @var string Prefix used for naming of encryption key cookies
     */
    protected $keyCookiePrefix = self::DEFAULT_KEY_COOKIE_PREFIX;

    /**
     * Factory method for returning instance of UltimateSessionConfig
     * based on configuration derived from combination of php.ini session.*
     * settings and parametric input.
     *
     * @param $useEncryption boolean
     * @param $keyCookiePrefix string
     * @return UltimateSessionConfig
     * @throws \InvalidArgumentException
     */
    public static function getInstance(
        $useEncryption = false,
        $keyCookiePrefix = self::DEFAULT_KEY_COOKIE_PREFIX
    ) {
        $handlerConfig = [];
        $handlerConfig['cookieDomain'] = ini_get('session.cookie_domain');
        $handlerConfig['cookieLifetime'] =
            (int)ini_get('session.cookie_lifetime');
        $handlerConfig['cookiePath'] = ini_get('session.cookie_path');
        $handlerConfig['cookieSecure'] = (bool)ini_get('session.cookie_secure');
        $handlerConfig['useEncryption'] = $useEncryption;
        $handlerConfig['keyCookiePrefix'] = $keyCookiePrefix;

        return new UltimateSessionConfig($handlerConfig);
    }

    /**
     * UltimateSessionConfig constructor. Expects associative array with
     * key values that exactly match this class' property names.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $setter = 'set' . ucfirst($key);
            $this->{$setter}($value);
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
        if (!property_exists(UltimateSessionConfig::class, $name)) {
            throw new \OutOfBoundsException(
                "The property '{$name}' you have tried to access does not exist."
            );
        }

        return $this->{$name};
    }

    /**
     * @param boolean $useEncryption
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setUseEncryption($useEncryption)
    {
        $this->validateBoolean($useEncryption);
        $this->useEncryption = $useEncryption;
    }

    /**
     * @param string $cookieDomain
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCookieDomain($cookieDomain)
    {
        $this->validateString($cookieDomain);
        $this->cookieDomain = $cookieDomain;
    }

    /**
     * @param int $cookieLifetime
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCookieLifetime($cookieLifetime)
    {
        $this->validateNonNegativeInteger($cookieLifetime);
        $this->cookieLifetime = $cookieLifetime;
    }

    /**
     * @param string $cookiePath
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCookiePath($cookiePath)
    {
        $this->validateString($cookiePath);
        $this->cookiePath = $cookiePath;
    }

    /**
     * @param bool $cookieSecure
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setCookieSecure($cookieSecure)
    {
        $this->validateBoolean($cookieSecure);
        $this->cookieSecure = $cookieSecure;
    }

    /**
     * @param string $keyCookiePrefix
     * @return void
     * @throws \InvalidArgumentException
     */
    public function setKeyCookiePrefix($keyCookiePrefix)
    {
        $this->validateNonEmptyString($keyCookiePrefix);
        $this->keyCookiePrefix = $keyCookiePrefix;
    }

    /**
     * @param $boolean
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateBoolean($boolean)
    {
        if (!is_bool($boolean)) {
            throw new \InvalidArgumentException('Value must be boolean.');
        }
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