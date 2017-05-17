MikeBrant\UltimateSessions\UltimateSessionHandler
===============

Class UltimateSessionHandler

A class which extends PHP's `SessionHandler` class with added encryption
capability. Class instantiation automatically sets php.ini session settings
to their preferred values and sets the class as session save handler via
`set_session_save_handler()`.

See article on \SessionHandler call pattern from PHP internals at
https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3

This functionality relies on excellent Defuse encryption library for PHP -
https://github.com/defuse/php-encryption


* Class name: UltimateSessionHandler
* Namespace: MikeBrant\UltimateSessions
* Parent class: SessionHandler
* This class implements: [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)




Properties
----------


### $config

    protected \MikeBrant\UltimateSessions\UltimateSessionHandlerConfig $config = null

Object storing session handler configuration values as derived from an
UltimateSessionHandleConfig object and needed for classes inheriting
UltimateSessionHandlerTrait.



* Visibility: **protected**


### $encryptionKey

    protected \Defuse\Crypto\Key $encryptionKey = null

Encryption key object as returned by
Defuse\Crypto\Key::createNewRandomKey()



* Visibility: **protected**


Methods
-------


### __construct

    \MikeBrant\UltimateSessions\UltimateSessionHandlerInterface MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::__construct(\MikeBrant\UltimateSessions\UltimateSessionHandlerConfig $config)

UltimateSessionHandlerInterface constructor.



* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $config **[MikeBrant\UltimateSessions\UltimateSessionHandlerConfig](MikeBrant-UltimateSessions-UltimateSessionHandlerConfig.md)**



### read

    string MikeBrant\UltimateSessions\UltimateSessionHandler::read(string $sessionId)

Overridden method to provide decryption during session read based on
config settings.



* Visibility: **public**


#### Arguments
* $sessionId **string**



### write

    boolean MikeBrant\UltimateSessions\UltimateSessionHandler::write(string $sessionId, string $sessionData)

Overridden method to provide encryption during session read based on
config settings.



* Visibility: **public**


#### Arguments
* $sessionId **string**
* $sessionData **string**



### sessionHandlerInit

    void MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::sessionHandlerInit()

Method to initialize session handler.  This method must be called in
constructor from any class inheriting UltimateSessionHandlerTrait.

This method must ultimately set the object implementing
UltimateSessionHandlerInterface as session handler using
session_set_save_handler().

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)




### validateSessionId

    void MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::validateSessionId($sessionId)

Method to validate session ID values for use by classes implementing
UltimateSessionHandlerInterface.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **mixed**



### setEncryptionKeyCookie

    void MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::setEncryptionKeyCookie(string $sessionId, string $asciiKey)

Method to set ASCII encryption key to cookie based on values derived from
configuration. This method both sends header via setcookie() and sets
$_COOKIE.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**
* $asciiKey **string**



### deleteEncryptionKeyCookie

    void MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::deleteEncryptionKeyCookie(string $sessionId)

Method to delete ASCII encryption key cookie. This method both sends
expired cookie header via setcookie and unsets the key from $_COOKIE.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**



### changeKeyCookieSessionId

    mixed MikeBrant\UltimateSessions\UltimateSessionHandler::changeKeyCookieSessionId($oldSessionId, $newSessionId)

Method to associate encryption key cookie to a new session id. Thi



* Visibility: **public**


#### Arguments
* $oldSessionId **mixed**
* $newSessionId **mixed**



### encrypt

    string MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::encrypt(string $sessionId, string $sessionData)

Method to perform encryption of session data.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**
* $sessionData **string**



### getEncryptionKey

    \Defuse\Crypto\Key MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::getEncryptionKey(string $sessionId)

Method that provides guaranteed return of Defuse\Crypto\Key object for
use in encryption.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**



### setEncryptionKey

    \Defuse\Crypto\Key MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::setEncryptionKey(string $sessionId)

Method which instantiates Defuse\Crypto\Key object and sets it on
encrpytionKey property in UltimateSessionHandlerTrait. Key can either be
recovered from encryption key cookie or created from scratch.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**



### decrypt

    string MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::decrypt(string $sessionId, string $sessionData)

Method to perform decryption of session data.

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $sessionId **string**
* $sessionData **string**



### configureIniSettings

    void MikeBrant\UltimateSessions\UltimateSessionHandler::configureIniSettings()

Make sure environment has proper php.ini settings for sessions.

Provide appropriate settings for session ID hash (PHP < 7.1) or random ID
generation (PHP 7.1+).

* Visibility: **private**




### setSessionHandler

    void MikeBrant\UltimateSessions\UltimateSessionHandler::setSessionHandler(\MikeBrant\UltimateSessions\UltimateSessionHandlerInterface $handler)

Method that sets the UltimateSessionHandlerInterface object to
session_set_save_handler().



* Visibility: **private**


#### Arguments
* $handler **[MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)**


