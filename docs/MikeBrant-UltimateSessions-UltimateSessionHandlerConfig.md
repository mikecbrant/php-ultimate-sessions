MikeBrant\UltimateSessions\UltimateSessionHandlerConfig
===============

Class UltimateSessionHandlerConfig

This class stores session configuration values used by
UltimateSessionHandlerTrait. This class also provides input validation for
values being set into php.ini.


* Class name: UltimateSessionHandlerConfig
* Namespace: MikeBrant\UltimateSessions



Constants
----------


### DEFAULT_KEY_COOKIE_PREFIX

    const DEFAULT_KEY_COOKIE_PREFIX = 'ULTSESSKEY_'





Properties
----------


### $useEncryption

    protected boolean $useEncryption = false

Flag determining if encryption is to be used for the session.



* Visibility: **protected**


### $cookieDomain

    protected string $cookieDomain = ''

Cookie domain to be used for encryption key cookie. This should match
domain used for session cookie.



* Visibility: **protected**


### $cookieLifetime

    protected integer $cookieLifetime

Cookie lifetime to be used for encryption key cookie. This should match
lifetime used for session cookie.



* Visibility: **protected**


### $cookiePath

    protected string $cookiePath = '/'

Cookie path to be used for encryption key cookie. This should match
path used for session cookie.



* Visibility: **protected**


### $cookieSecure

    protected boolean $cookieSecure = false

Cookie security setting to be used for encryption key cookie. This
should match setting used for session cookie.



* Visibility: **protected**


### $keyCookiePrefix

    protected string $keyCookiePrefix = self::DEFAULT_KEY_COOKIE_PREFIX

Prefix used for determining name used for encryption key cookie.



* Visibility: **protected**


### 

    public string 

keyCookiePrefix



* Visibility: **public**


Methods
-------


### getInstance

    \MikeBrant\UltimateSessions\UltimateSessionHandlerConfig MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::getInstance($useEncryption, $keyCookiePrefix)

Factory method for returning instance of UltimateSessionHandlerConfig
based on configuration derived from combination of php.ini session.*
settings and parametric input. This is typically the preferred method
for instantiating this class unless you have specific reason to vary
from default settings.



* Visibility: **public**
* This method is **static**.


#### Arguments
* $useEncryption **mixed** - &lt;p&gt;boolean&lt;/p&gt;
* $keyCookiePrefix **mixed** - &lt;p&gt;string&lt;/p&gt;



### __construct

    mixed MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::__construct(array $config)

UltimateSessionHandlerConfig constructor. Expects associative array with
key values that exactly match this class' property names.



* Visibility: **public**


#### Arguments
* $config **array**



### __get

    mixed MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::__get(string $name)

Magic getter to provide public-like access to config.



* Visibility: **public**


#### Arguments
* $name **string**



### setUseEncryption

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setUseEncryption(boolean $useEncryption)





* Visibility: **public**


#### Arguments
* $useEncryption **boolean**



### setCookieDomain

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setCookieDomain(string $cookieDomain)





* Visibility: **public**


#### Arguments
* $cookieDomain **string**



### setCookieLifetime

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setCookieLifetime(integer $cookieLifetime)





* Visibility: **public**


#### Arguments
* $cookieLifetime **integer**



### setCookiePath

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setCookiePath(string $cookiePath)





* Visibility: **public**


#### Arguments
* $cookiePath **string**



### setCookieSecure

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setCookieSecure(boolean $cookieSecure)





* Visibility: **public**


#### Arguments
* $cookieSecure **boolean**



### setKeyCookiePrefix

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::setKeyCookiePrefix(string $keyCookiePrefix)





* Visibility: **public**


#### Arguments
* $keyCookiePrefix **string**



### validateBoolean

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::validateBoolean($boolean)





* Visibility: **protected**


#### Arguments
* $boolean **mixed**



### validateInteger

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::validateInteger($integer)





* Visibility: **protected**


#### Arguments
* $integer **mixed**



### validateNonNegativeInteger

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::validateNonNegativeInteger($integer)





* Visibility: **protected**


#### Arguments
* $integer **mixed**



### validateString

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::validateString($string)





* Visibility: **protected**


#### Arguments
* $string **mixed**



### validateNonEmptyString

    void MikeBrant\UltimateSessions\UltimateSessionHandlerConfig::validateNonEmptyString($string)





* Visibility: **protected**


#### Arguments
* $string **mixed**


