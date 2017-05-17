MikeBrant\UltimateSessions\UltimateSessionManager
===============

Class UltimateSessionManager

This class is an object-oriented wrapper around common PHP session management
functions along with features geared at enhancing security
and proper session management behaviors including:
- timestamp-based management for session data expiry;
- automated session ID regeneration at configurable time- and count-based
intervals;
- client fingerprinting based on request header properties
- logging of data for certain use cases where there are session accesses
against expired data or accesses with mis-matched fingerprints, which may
need further investigation. Only data within session metadata is logged.
An optional PSR-3 compliant logger to be used for logging in lieu of
default logging via `error_log()`.

This class allows for setting of callback around session ID change events
(ID regeneration or ID forwarding from expired session). For example, in
recommended library configuration, this callback is used to trigger
encryption key cookie regeneration using
`UltimateSessionHandler::changeKeyCookieSessionId()`.

For cases where custom session garbage collection is implemented
(something that should strongly be considered for production-level
applications), this class offers setting of an optional callback that
is passed session ID and data expiry timestamp that can be used to mark
those session ID's as eligible for garbage collection after given
timestamp (by touching files, updating database field, etc.).


* Class name: UltimateSessionManager
* Namespace: MikeBrant\UltimateSessions
* This class implements: Psr\Log\LoggerAwareInterface


Constants
----------


### METADATA_KEY

    const METADATA_KEY = 'ultSessionMetadata'





Properties
----------


### $config

    protected \MikeBrant\UltimateSessions\UltimateSessionManagerConfig $config

Stores UltimateSessionHandlerConfig object as passed from
UltimateSessionHandler.



* Visibility: **protected**


### $metadata

    protected \MikeBrant\UltimateSessions\UltimateSessionManagerMetadata $metadata

An UltimateSessionManagerMetadata object which stores all the session
metadata that UltimateSessionManager injects into $_SESSION to manage
session security.



* Visibility: **protected**


### $sessionIdChangeCallback

    protected callable $sessionIdChangeCallback

Property which stores an optional callback that is triggered when a
session ID change event occurs. This could, for example, be used
in conjunction with
UltimateSessionHandlerTrait::changeKeyCookieSessionId() method to
change the cookie where the session data encryption key is stored.

This callable should have the following signature:

function (string $oldSessionId, string $newSessionId) { ... }

* Visibility: **protected**


### $gcNotificationCallback

    protected callable $gcNotificationCallback

Property which stores an optional callback for use when implementing
session handlers which require custom garbage collection logic. This
callback can be used to mark a particular session ID as eligible for
garbage collection after a given timestamp.  This callable should have
the following signature:

function (string $sessionId, int $expiryTimestamp) { ... }

* Visibility: **protected**


### $logger

    protected \Psr\Log\LoggerInterface $logger

Stores optional PSR-3 compliant logger object



* Visibility: **protected**


Methods
-------


### __construct

    mixed MikeBrant\UltimateSessions\UltimateSessionManager::__construct(\MikeBrant\UltimateSessions\UltimateSessionManagerConfig $config, callable|null $sessionIdChangeCallback, callable|null $gcNotificationCallback, \Psr\Log\LoggerInterface|null $logger)

UltimateSessionManager constructor.

This method requires injection of UltimateSessionManagerConfig object.

Since default configuration values should suffice for most use
cases, the following would be typical usage to instantiate configuration
into UltimateSessionManager:

$session = new UltimateSessionManager(new UltimateSessionManagerConfig);

This method also supports optional injection of a PSR-3 compliant
logger adhering to Psr\Log\LoggerInterface

* Visibility: **public**


#### Arguments
* $config **[MikeBrant\UltimateSessions\UltimateSessionManagerConfig](MikeBrant-UltimateSessions-UltimateSessionManagerConfig.md)**
* $sessionIdChangeCallback **callable|null**
* $gcNotificationCallback **callable|null**
* $logger **Psr\Log\LoggerInterface|null**



### startSession

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::startSession()

Method to start session using session_start() and inject security
metadata into $_SESSION. Method returns true on success and false on
failure.  If this method throws or returns false, you should consider
this an insecure session and remove any elevated privileges requestor
may have (i.e. consider them un-authenticated).



* Visibility: **public**




### commitSession

    void MikeBrant\UltimateSessions\UltimateSessionManager::commitSession()

Method which wraps PHP's session_write_close() function enabling
caller to close session to further modifications.



* Visibility: **public**




### regenerateId

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::regenerateId()

This method generates new session ID while also writing data expiry
and session ID forwarding information to current session. This method
calls forwardSession() method with generated ID to actually trigger
session ID change. This method should be used by external callers to
trigger session ID regeneration after critical state changes in the
application such as login/logout, privilege escalation, etc.

For PHP 7.1.0+ session_create_id() is used to generate new session ID to
be passed to forwardSession(). For PHP < 7.1.0 session_regenerate_id()
is called to create new session id, but session is then
reverted to old session ID to add session forwarding information before
calling forwardSession();

This method not include in coverage, though it is unit tests as large
portion of logic is dependent on PHP version.  Coverage is provided
through tests in build environment.

* Visibility: **public**




### destroySession

    void MikeBrant\UltimateSessions\UltimateSessionManager::destroySession()

Method to fully unset all session data and close session. Note this
method does not destroy the session cookie, which is not necessary
when running session using strict mode, which is a baseline requirement
for using this library.

This method can be used by external callers to immediately disable all
data access to an existing session and close the session for rare
cases (i.e. expected security breach) where application logic may
dictate such usage.

* Visibility: **public**




### getSessionId

    string MikeBrant\UltimateSessions\UltimateSessionManager::getSessionId()

Method which provides wrapper around PHP's session_id() function in a
read-only context.



* Visibility: **public**




### getSessionName

    string MikeBrant\UltimateSessions\UltimateSessionManager::getSessionName()

Method which provides wrapper around PHP's session_name() function in
a read-only context.



* Visibility: **public**




### initializeSessionMetadata

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::initializeSessionMetadata()

Method used to instantiate a new set of metadata into the session as
would typically happen on first session start or after session ID
regeneration events.



* Visibility: **protected**




### continueSession

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::continueSession()

Method which continues a session that is considered valid, checking
whether that session either needs to be forwarded to a new session ID
based on recent session ID regeneration event, or whether the session
has exceeded configured threshold for a forced session ID regeneration.

If neithed of these cases is true, this method simple increments the
session start counter in session metadata.

* Visibility: **protected**




### forwardSession

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::forwardSession(string $newSessionId)

This method accepts a session ID as generated by session_create_id(),
closes existing session and then initiates new session with passed
session ID.



* Visibility: **protected**


#### Arguments
* $newSessionId **string**



### isSessionValid

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::isSessionValid()

Method which determines if current session id valid by verifying the
session fingerprint as well as whether the session is either the
currently active session or a session that is eligible to be forwarded
to a currently active session based on current timestamp being less
than the data expiry timestamp of session on which ID regeneration has
been performed.



* Visibility: **protected**




### isValidFingerprint

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::isValidFingerprint()

Method which compares session fingerprint hash in session metadata
against hash formed from current request to determine if the session
cna reasonable be expected to be from the same client.



* Visibility: **protected**




### isForwardedSession

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::isForwardedSession()

Method which uses session metadata to determine if current request is
coming from session ID that has been regenerated and is still within
expiry TTL such that it can be forwarded to new session.



* Visibility: **protected**




### needsIdRegen

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::needsIdRegen()

Method which uses session metadata to determine of current session ID
should be regenerated either because the session regeneration
timestamp has passed or the number of session_start() calls has
exceeded the configured threshold.



* Visibility: **protected**




### writeMetadata

    void MikeBrant\UltimateSessions\UltimateSessionManager::writeMetadata()

Method to persist the metadata object to $_SESSION superglobal.



* Visibility: **protected**




### generateFingerprint

    string MikeBrant\UltimateSessions\UltimateSessionManager::generateFingerprint()

Method to generate a fingerprint hash from client information
available in $_SERVER superglobal. This hash should be considered to
be stable for a client during any given session.



* Visibility: **protected**




### executeSessionIdChangeCallback

    void MikeBrant\UltimateSessions\UltimateSessionManager::executeSessionIdChangeCallback($oldSessionId, $newSessionId)





* Visibility: **protected**


#### Arguments
* $oldSessionId **mixed**
* $newSessionId **mixed**



### executeGcNotificationCallback

    void MikeBrant\UltimateSessions\UltimateSessionManager::executeGcNotificationCallback($sessionId, $expiryTimestamp)

Method which executes garbage collection notification callback if set
on object.  This method will forward the session ID and data expiry
timestamp passed as parameters to the callback.



* Visibility: **protected**


#### Arguments
* $sessionId **mixed**
* $expiryTimestamp **mixed**



### setLogger

    void MikeBrant\UltimateSessions\UltimateSessionManager::setLogger(\Psr\Log\LoggerInterface $logger)

Method to allow optional setting of PSR-3 compliant logger for session
logging.



* Visibility: **public**


#### Arguments
* $logger **Psr\Log\LoggerInterface**



### log

    boolean MikeBrant\UltimateSessions\UltimateSessionManager::log(string $message, string $level)

Logging method.



* Visibility: **protected**


#### Arguments
* $message **string**
* $level **string**


