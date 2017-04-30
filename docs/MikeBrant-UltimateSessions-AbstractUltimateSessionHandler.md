MikeBrant\UltimateSessions\AbstractUltimateSessionHandler
===============

Class AbstractUltimateSessionHandler

This is abstract base class for extension for cases where one does not
want to directly inherit from PHP's \SessionHandler class as is done in
UltimateSessionConfig, but still want to leverage the functionality
provided in UltimateSessionHandlerTrait which is composed into both classes.

For most use cases, it would likely be preferable to extend
UltimateSessionHandler rather than this class in order to provide your own
custom storage mechanism, as that class inherits
\SessionHandler::create_sid() method which is missing from this class as
it is not a part of \SessionHandlerInterface.

This is contradictory to the warning shown in \SessionHandler documentation
on PHP.net, but is the approach that has been identified to best work
around to the issue with the missing create_id() method in
\SessionHandlerInterface. See https://github.com/php/php-src/pull/109 for
more info from PHP source developers.

Without being able to handle the create_sid() call directly for use in
updating what storage mechanism a class inheriting from this might utilize,
you likely will need to manage ID validation/invalidation within open
method implementation.

See article on \SessionHandler call pattern from PHP internals at
https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3


* Class name: AbstractUltimateSessionHandler
* Namespace: MikeBrant\UltimateSessions
* This is an **abstract** class
* This class implements: [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)




Properties
----------


### $config

    protected \MikeBrant\UltimateSessions\UltimateSessionConfig $config = null

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

    \MikeBrant\UltimateSessions\UltimateSessionHandlerInterface MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::__construct($useEncryption, $encryptionCookiePrefix)

UltimateSessionHandlerInterface constructor.



* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $useEncryption **mixed**
* $encryptionCookiePrefix **mixed**



### close

    boolean MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::close()

Abstract method from \SessionHandlerInterface.



* Visibility: **public**
* This method is **abstract**.




### destroy

    boolean MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::destroy(string $sessionId)

Abstract method from \SessionHandlerInterface.



* Visibility: **public**
* This method is **abstract**.


#### Arguments
* $sessionId **string**



### gc

    boolean MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::gc(integer $maxLifetime)

Abstract method from \SessionHandlerInterface.



* Visibility: **public**
* This method is **abstract**.


#### Arguments
* $maxLifetime **integer**



### open

    boolean MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::open(string $savePath, string $name)

Abstract method from \SessionHandlerInterface.



* Visibility: **public**
* This method is **abstract**.


#### Arguments
* $savePath **string**
* $name **string**



### read

    string MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::read(string $sessionId)

Abstract method from \SessionHandlerInterface.

When implemented this method should conditionally call $this->decrypt()
based on setting value for $this->config->useEncryption

* Visibility: **public**
* This method is **abstract**.


#### Arguments
* $sessionId **string**



### write

    boolean MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::write(string $sessionId, string $sessionData)

Abstract method from \SessionHandlerInterface.

When implemented this method should conditionally call $this->encrypt()
based on setting value for $this->config->useEncryption

* Visibility: **public**
* This method is **abstract**.


#### Arguments
* $sessionId **string**
* $sessionData **string**



### sessionHandlerInit

    void MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::sessionHandlerInit($useEncryption, $encryptionCookiePrefix)

Method to initialize session handler.  This method must be called in
constructor from any class inheriting UltimateSessionHandlerTrait.

This method must ultimately set the object implementing
UltimateSessionHandlerInterface as session handler using
session_set_save_handler().

This method is implemented in UltimateSessionHandlerTrait.

* Visibility: **public**
* This method is defined by [MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)


#### Arguments
* $useEncryption **mixed** - &lt;p&gt;boolean&lt;/p&gt;
* $encryptionCookiePrefix **mixed** - &lt;p&gt;string&lt;/p&gt;



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

    \MikeBrant\UltimateSessions\Key MikeBrant\UltimateSessions\UltimateSessionHandlerInterface::setEncryptionKey(string $sessionId)

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

    void MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::configureIniSettings()

Make sure environment has proper php.ini settings for sessions.

Provide appropriate settings for session ID hash (PHP < 7.1) or random ID
generation (PHP 7.1+).

* Visibility: **private**




### setSessionHandler

    void MikeBrant\UltimateSessions\AbstractUltimateSessionHandler::setSessionHandler(\MikeBrant\UltimateSessions\UltimateSessionHandlerInterface $handler)

Method that sets the UltimateSessionHandlerInterface object to
session_set_save_handler().



* Visibility: **private**


#### Arguments
* $handler **[MikeBrant\UltimateSessions\UltimateSessionHandlerInterface](MikeBrant-UltimateSessions-UltimateSessionHandlerInterface.md)**


