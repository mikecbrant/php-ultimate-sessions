MikeBrant\UltimateSessions\UltimateSessionManager
===============






* Class name: UltimateSessionManager
* Namespace: MikeBrant\UltimateSessions





Properties
----------


### $dataDestructionDelaySecs

    protected integer $dataDestructionDelaySecs = 10

Setting to determine how many seconds after session id regeneration that
data is to remain available for read by proper validated session.  This
is to mitigate race conditions due to concurrent requests. Session
must still be considered invalid during this time period.



* Visibility: **protected**



