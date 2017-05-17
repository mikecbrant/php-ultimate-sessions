MikeBrant\UltimateSessions\UltimateSessionManagerConfig
===============

Class UltimateSessionManagerConfig

Configuration object which is passed to UltimateSessionManager class as
dependency.  The default configuration values should suffice for most use
cases, so the following would be typical usage to instantiate configuration
into UltimateSessionManager:

$session = new UltimateSessionManager(new UltimateSessionManagerConfig);


* Class name: UltimateSessionManagerConfig
* Namespace: MikeBrant\UltimateSessions





Properties
----------


### $regenIdInterval

    protected integer $regenIdInterval = 15

Value to determine time (in minutes) after which forced session
id regeneration will occur. Must be non-negative integer value. A
value of 0 indicates no time-based id rotation.



* Visibility: **protected**


### $regenIdCount

    protected integer $regenIdCount = 10

Value to determine the number of session_start events after which
forced session id regeneration will occur. Must be non-negative
integer value. A value of 0 indicates no forced id rotation.



* Visibility: **protected**


### $ttlAfterIdRegen

    protected integer $ttlAfterIdRegen = 30

Value to determine how long (in seconds) past session
id regeneration event to no longer allow access to session data to
formerly valid session. Must be non-negative integer value.



* Visibility: **protected**


### 

    public integer 

ttlAfterIdRegen



* Visibility: **public**


Methods
-------


### __construct

    mixed MikeBrant\UltimateSessions\UltimateSessionManagerConfig::__construct(integer $regenIdInterval, integer $regenIdCount, integer $ttlAfterIdRegen)

UltimateSessionManagerConfig constructor. Suggested usage is to not
pass any arguments to constructor unless you have specific reason to
vary from default values.



* Visibility: **public**


#### Arguments
* $regenIdInterval **integer**
* $regenIdCount **integer**
* $ttlAfterIdRegen **integer**



### __get

    mixed MikeBrant\UltimateSessions\UltimateSessionManagerConfig::__get(string $name)

Magic getter to provide public-like access to config.



* Visibility: **public**


#### Arguments
* $name **string**



### setRegenIdInterval

    void MikeBrant\UltimateSessions\UltimateSessionManagerConfig::setRegenIdInterval(integer $minutes)





* Visibility: **public**


#### Arguments
* $minutes **integer**



### setRegenIdCount

    void MikeBrant\UltimateSessions\UltimateSessionManagerConfig::setRegenIdCount(integer $count)





* Visibility: **public**


#### Arguments
* $count **integer**



### setTtlAfterIdRegen

    void MikeBrant\UltimateSessions\UltimateSessionManagerConfig::setTtlAfterIdRegen(integer $seconds)





* Visibility: **public**


#### Arguments
* $seconds **integer**



### validateInteger

    void MikeBrant\UltimateSessions\UltimateSessionManagerConfig::validateInteger($integer)





* Visibility: **protected**


#### Arguments
* $integer **mixed**



### validateNonNegativeInteger

    void MikeBrant\UltimateSessions\UltimateSessionManagerConfig::validateNonNegativeInteger($integer)





* Visibility: **protected**


#### Arguments
* $integer **mixed**


