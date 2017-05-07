<?php

namespace MikeBrant\UltimateSessions;

/**
 * Class AbstractUltimateSessionHandler
 *
 * This is abstract base class for extension for cases where one does not
 * want to directly inherit from PHP's \SessionHandler class as is done in
 * UltimateSessionHandlerConfig, but still want to leverage the functionality
 * provided in UltimateSessionHandlerTrait which is composed into both classes.
 *
 * For most use cases, it would likely be preferable to extend
 * UltimateSessionHandler rather than this class in order to provide your own
 * custom storage mechanism, as that class inherits
 * \SessionHandler::create_sid() method which is missing from this class as
 * it is not a part of \SessionHandlerInterface.
 *
 * This is contradictory to the warning shown in \SessionHandler documentation
 * on PHP.net, but is the approach that has been identified to best work
 * around to the issue with the missing create_id() method in
 * \SessionHandlerInterface. See https://github.com/php/php-src/pull/109 for
 * more info from PHP source developers.
 *
 * Without being able to handle the create_sid() call directly for use in
 * updating what storage mechanism a class inheriting from this might utilize,
 * you likely will need to manage ID validation/invalidation within open()
 * method implementation.
 *
 * See article on \SessionHandler call pattern from PHP internals at
 * https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3
 *
 * @package MikeBrant\UltimateSessions
 * @codeCoverageIgnore
 */
abstract class AbstractUltimateSessionHandler implements UltimateSessionHandlerInterface
{
    use UltimateSessionHandlerTrait;

    /**
     * AbstractUltimateSessionHandler constructor. All inheriting classes
     * should call this constructor such that session handler is registered
     * properly.
     *
     * @param UltimateSessionHandlerConfig $config
     * @throws \Exception
     */
    public function __construct(UltimateSessionHandlerConfig $config)
    {
        $this->config = $config;
        $this->sessionHandlerInit();
    }

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * @return boolean
     */
    abstract function close();

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * @param string $sessionId
     * @return boolean
     */
    abstract function destroy($sessionId);

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * @param int $maxLifetime
     * @return boolean
     */
    abstract function gc($maxLifetime);

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * @param string $savePath
     * @param string $name
     * @return boolean
     */
    abstract function open($savePath, $name);

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * When implemented this method should conditionally call $this->decrypt()
     * based on setting value for $this->config->useEncryption
     *
     *
     * @param string $sessionId
     * @return string
     */
    abstract function read($sessionId);

    /**
     * Abstract method from \SessionHandlerInterface.
     *
     * When implemented this method should conditionally call $this->encrypt()
     * based on setting value for $this->config->useEncryption
     *
     * @param string $sessionId
     * @param string $sessionData
     * @return boolean
     */
    abstract function write($sessionId, $sessionData);
}