MikeBrant\UltimateSessions\UltimateSessionManagerMetadata
===============

Class UltimateSessionManagerMetadata

This class represents simple structured storage object for session metadata
which UltimateSessionManager class will set into $_SESSION superglobal for
session management.  This class is intentionally left mutable as the
UltimateSessionManager class truly owns this data. This class is provided
really just to provide separation of metadata storage model from consuming
session manager as well as convenient instantiation for setting this
structure in a clean state upon generation of new session ID's.


* Class name: UltimateSessionManagerMetadata
* Namespace: MikeBrant\UltimateSessions
* This class implements: JsonSerializable




Properties
----------


### $instantiatedAt

    public integer $instantiatedAt





* Visibility: **public**


### $regenerateIdAt

    public integer $regenerateIdAt





* Visibility: **public**


### $sessionStartCount

    public integer $sessionStartCount





* Visibility: **public**


### $isActive

    public boolean $isActive





* Visibility: **public**


### $expireDataAt

    public integer $expireDataAt





* Visibility: **public**


### $forwardToSessionId

    public string $forwardToSessionId





* Visibility: **public**


### $fingerprint

    public string $fingerprint





* Visibility: **public**


Methods
-------


### __construct

    mixed MikeBrant\UltimateSessions\UltimateSessionManagerMetadata::__construct()

UltimateSessionManagerMetadata constructor.



* Visibility: **public**




### jsonSerialize

    array MikeBrant\UltimateSessions\UltimateSessionManagerMetadata::jsonSerialize()

Method implementing JsonSerializable interface to allow object to be
serialized to JSON.



* Visibility: **public**



