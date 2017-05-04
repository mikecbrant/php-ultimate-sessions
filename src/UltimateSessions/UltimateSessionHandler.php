<?php

namespace MikeBrant\UltimateSessions;

/**
 * Class UltimateSessionHandler
 *
 * This class extends PHP's \SessionHandler implementation to use
 * UltimateSession functionality exposed through UltimateSessionHandlerTrait.
 * This class provides optional encryption of data stored in session.
 *
 * See article on \SessionHandler call pattern from PHP internals at
 * https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3
 *
 * This functionality relies on excellent Defuse encryption library for PHP -
 * https://github.com/defuse/php-encryption
 *
 * @see AbstractSessionHandler for considerations on extending this class
 * (the preferred approach) vs. implementing AbstractSessionHandler.
 * @package MikeBrant\UltimateSessions
 */
class UltimateSessionHandler extends \SessionHandler implements UltimateSessionHandlerInterface
{
    use UltimateSessionHandlerTrait;

    /**
     * UltimateSessionHandler constructor.
     *
     * @param UltimateSessionConfig $config
     * @throws \Exception
     */
    public function __construct(UltimateSessionConfig $config) {
        $this->config = $config;
        $this->sessionHandlerInit();
    }

    /**
     * Overridden method to provide decryption during session read based on
     * config settings.
     *
     * @param string $sessionId
     * @return string
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException
     * @throws \InvalidArgumentException
     */
    public function read($sessionId) {
        $this->validateSessionId($sessionId);
        $sessionData = parent::read($sessionId);
        if($this->config->useEncryption && !empty($sessionData)) {
            return $this->decrypt($sessionId, $sessionData);
        }
        return $sessionData;
    }

    /**
     * Overridden method to provide encryption during session read based on
     * config settings.
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return bool
     * @throws \Defuse\Crypto\Exception\BadFormatException
     * @throws \Defuse\Crypto\Exception\EnvironmentIsBrokenException
     * @throws \InvalidArgumentException
     */
    public function write($sessionId, $sessionData) {
        $this->validateSessionId($sessionId);
        if($this->config->useEncryption) {
            $sessionData = $this->encrypt($sessionId, $sessionData);
        }
        return parent::write($sessionId, $sessionData);
    }
}