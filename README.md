[![Build Status](https://travis-ci.org/mikecbrant/php-ultimate-sessions.svg?branch=master)](https://travis-ci.org/mikecbrant/php-ultimate-sessions)
[![Code Climate](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/badges/gpa.svg)](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions)
[![Test Coverage](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/badges/coverage.svg)](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/coverage)

# php-ultimate-sessions

This library provides a simple drop-in replacement to PHP's out-of-the-box 
session functionality to provide more robust and secure session handling by 
enforcing recommended php.ini session settings and providing optional session 
data encryption, with client-side decryption key storage (in cookie). 
Encryption is provided via use of the excellent [defuse/php-encryption](https://github.com/defuse/php-encryption) library.

**The primary components of the library are:**

[UltimateSessionHandler](docs/MikeBrant-UltimateSessions-UltimateSessionHandler.md)

A class which extends PHP's `SessionHandler` class with added encryption
capability. Class instantiation automatically sets php.ini session settings
to their preferred values and sets the class as session save handler via
`set_session_save_handler()`.

[UltimateSessionManager](doc/MikeBrant-UltimateSessions-UltimateSessionManager.md)

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

[UltimateSessionLoader](doc/)

A simple static interface provided to allow for one-line instantiation of
the UltimateSession library using recommended settings, with a few minimal
configuration options.

Requires:
- PHP OpenSSL extension (if using encryption)
- [defuse/php-encryption](https://github.com/defuse/php-encryption) library
- PHPUnit 6.1+ (for unit tests only)

This library is tested via Travis CI against:
- PHP 7.0.*
- PHP 7.1.*
- PHP Nightly build

[Full Library Documentation](docs/UltimateSession.md)

[Travis CI build status](https://travis-ci.org/mikecbrant/php-ultimate-sessions)

[Code Climate code coverage and health information](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions)

[Packagist page](https://packagist.org/packages/mikecbrant/php-ultimate-sessions)


**Usage**

Usage is intended to be dead simple.  Using 
`UltimateSessionLoader::initialize()` you can 
set up with few lines of code. The parameters for this method are:

- `$useEncryption` (boolean, default `false`) Flag to determine if encryption
 is applied to session data.
- `$logger` (Psr\Log\LoggerInterface, default null) A PSR-3 compliant logger 
can be attached to UltimateSessionManager to allow for logging of 
non-standard session access events.

Sample code usage:

```php

use \MikeBrant\UltimateSessions;

try {
    $sessionManager = UltimateSessionLoader::initialize(
        $useEncryption,
        $logger
    );
} catch (\Exception $e) {
    // handle as needed (or don't try-catch at all)
}
// replacement for session_start()
$result = $sessionManager->startSession();

// Make sure to investigate result of startSession()
// as false represents a case where an invalid session was attempted
// to be started
if($result === false) {
    // perhaps exit execution
    // at a minimum drop all user authentication/state
}

// set session data as normal
$_SESSION['data'] = 'value';

// manually regenerate session Id
// as typically done around authentication or privelege level change
$sessionManager->regenerateId();

// get session ID
$sessionId = $sessionManager->getSessionId();

// close session to write, replacement for session_write_commit()
$sessionManager->commitSession();

// destroy session. Not commonly used, by calling code, but can be
// used as necessary to unset $_SESSION and destroy session as done
// with session_destroy()
$sessionManager->destroySession();
```

There are more configuration options available beyond this Please look at 
full library documentation at link above.

Note that while the `UltimateSessionHandler` and `UltimateSessionManager` are
designed to work together, each of these classes could be used independently 
of each other.  The `UltimateSessionHandler` class should also be considered
as a class designed to be extended upon should one want to implement custom
session storage (i.e. in database, memory store, etc.) above and beyond 
one of the built-in PHP session stores which which this class can work as-is.

**A note on session garbage collection:**


This library does not perform session garbage collection or override default
`SassionHandler::gc()` behavior at all.  As such, this class would work with
PHP's built in session stores and not modify their basic behavior at all. If one were to extend `UltimateSessionHandler` to implement a custom data 
store it is **highly** recommended that you not rely on PHP's default probability-based garbage collection scheme (and thus not override `gc()` 
method at all).  It is preferable to use a schedule-based garbage collection
mechanism as a means to perform final deletion of session stores.  To this 
end, the `UltimateSessionManager` class constructor accepts a callback 
which can be used as a hook for you to update your session data store with 
a timestamp after which the individual data for the session can be removed.
This hook can actually be used even in cases where you want to override 
`gc()` in a probability-based deletion scheme (the probability-based 
approach triggers `gc()` session handler).  You would just need your 
overridden `gc()` method to take advantage of the timestamps that have 
been set on the data store to determine eligibility of the session data 
for deletion.
   
**Recommended reading related to PHP session security:**

[PHP Session Lifecycle](https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3)

[PHP.net Session Management Basics](http://php.net/manual/en/features.session.security.management.php)

[PHP.net Securing Session INI Settings](http://php.net/manual/en/session.security.ini.php)

[PHP SessionHandler Class](http://php.net/manual/en/class.sessionhandler.php)