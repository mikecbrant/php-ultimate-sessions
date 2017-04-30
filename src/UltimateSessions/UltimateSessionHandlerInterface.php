<?php

namespace MikeBrant\UltimateSessions;

/**
 * Interface UltimateSessionHandlerInterface
 *
 * This interface relates to UltimateSessionHandlerTrait and represents, in
 * essence, the abstraction of the concrete functionality supplied in that
 * trait, along with the base session handling method abstractions inherited
 * from \SessionHandlerInterface. A class implementing this interface is
 * intended to also use this trait.
 *
 * This interface also holds constants representing required php.ini session
 * .* settings as required by the UltimateSessions library.
 * UltimateSessionHandlerTrait uses these constants for setting php.ini
 * session.* values to required state.  The settings are in accordance with
 * best practices described on PHP.net at:
 * http://php.net/manual/en/features.session.security.management.php and
 * http://php.net/manual/en/session.security.ini.php
 *
 * This library does not enforce a session cookie lifetime value, but in most
 * cases the default value of 0 is most appropriate.
 *
 * This functionality relies on excellent Defuse encryption library for PHP -
 * https://github.com/defuse/php-encryption
 *
 * @package MikeBrant\UltimateSessions
 */
interface UltimateSessionHandlerInterface extends \SessionHandlerInterface
{
    /**
     * Array representing php.ini settings that are required for
     * compatibility with UltimateSessions.
     *
     * @var array Required php.ini settings
     */
    const REQUIRED_INI_SETTINGS = [
        'session.use_strict_mode' => '1',
        'session.use_cookies' => '1',
        'session.use_only_cookies' => '1',
        'session.cookie_httponly' => '1',
        'session.use_trans_sid' => '0',
        'session.cache_limiter' => 'nocache',
        'session.serialize_handler' => 'php_serialize'
    ];

    /**
     * Array representing php.ini settings that are required for
     * compatibility with UltimateSessions in PHP >= 7.1.0
     *
     * @var array Required php.in settings for PHP >= 7.1.0
     */
    const REQUIRED_INI_SETTINGS_PHP_7_1_0 = [
        'session.sid_length' => '48',
        'session.sid_bits_per_character' => '5'
    ];

    /**
     * Array representing php.ini settings that are required for
     * compatibility with UltimateSessions in PHP < 7.1.0
     *
     * @var array Required php.in settings for PHP < 7.1.0
     */
    const REQUIRED_INI_SETTINGS_PHP_BELOW_7_1_0 = [
        'session.hash_function' => 'sha256',
        'session.hash_bits_per_character' => '4',
        'session.entropy_length' => '32'
    ];

    /**
     * String representing expected regex pattern for session id validation
     * for use in UltimateSessions in PHP >= 7.1.0.
     *
     * @var string Session ID regex for PHP >= 7.1.0
     */
    const SESSION_ID_REGEX_PHP_7_1_0 = '/^[0-9a-v]{48}$/';

    /**
     * String representing expected regex pattern for session id validation
     * for use in UltimateSessions in PHP < 7.1.0.
     *
     * @var string Session ID regex for PHP < 7.1.0
     */
    const SESSION_ID_REGEX_PHP_BELOW_7_1_0 = '/^[0-9a-f]{64}$/';

    /**
     * UltimateSessionHandlerInterface constructor.
     *
     * @param $useEncryption
     * @param $encryptionCookiePrefix
     * @return self
     */
    function __construct($useEncryption, $encryptionCookiePrefix);

    /**
     * Method to set ASCII encryption key to cookie based on values derived from
     * configuration. This method both sends header via setcookie() and sets
     * $_COOKIE.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @param string $asciiKey
     * @return void
     */
    function setEncryptionKeyCookie($sessionId, $asciiKey);

    /**
     * Method to delete ASCII encryption key cookie. This method both sends
     * expired cookie header via setcookie and unsets the key from $_COOKIE.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @return void
     */
    function deleteEncryptionKeyCookie($sessionId);

    /**
     * Method to perform encryption of session data.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return string
     */
    function encrypt($sessionId, $sessionData);

    /**
     * Method that provides guaranteed return of Defuse\Crypto\Key object for
     * use in encryption.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @return \Defuse\Crypto\Key
     */
    function getEncryptionKey($sessionId);

    /**
     * Method which instantiates Defuse\Crypto\Key object and sets it on
     * encrpytionKey property in UltimateSessionHandlerTrait. Key can either be
     * recovered from encryption key cookie or created from scratch.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @return \Defuse\Crypto\Key
     */
    function setEncryptionKey($sessionId);

    /**
     * Method to perform decryption of session data.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return string
     */
    function decrypt($sessionId, $sessionData);

    /**
     * Method to validate session ID values for use by classes implementing
     * UltimateSessionHandlerInterface.
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param $sessionId
     * @return void
     */
    function validateSessionId($sessionId);

    /**
     * Method to initialize session handler.  This method must be called in
     * constructor from any class inheriting UltimateSessionHandlerTrait.
     * This method must ultimately set the object implementing
     * UltimateSessionHandlerInterface as session handler using
     * session_set_save_handler().
     *
     * This method is implemented in UltimateSessionHandlerTrait.
     *
     * @param $useEncryption boolean
     * @param $encryptionCookiePrefix string
     * @return void
     */
    function sessionHandlerInit($useEncryption, $encryptionCookiePrefix);
}