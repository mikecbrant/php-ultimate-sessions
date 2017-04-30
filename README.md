[![Build Status](https://travis-ci.org/mikecbrant/php-ultimate-sessions.svg?branch=master)](https://travis-ci.org/mikecbrant/php-ultimate-sessions)
[![Code Climate](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/badges/gpa.svg)](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions)
[![Test Coverage](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/badges/coverage.svg)](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions/coverage)

# php-ultimate-sessions

This library provides a simple drop-in replacement to PHP's out-of-the-box 
session functionality to provide more robust and secure session handling by 
enforcing recommend php.ini session settings and providing optional session 
data encryption, with client-side decryption key storage (in cookie). 
Encryption is provided via use of the excellent [defuse/php-encryption](https://github.com/defuse/php-encryption) library.

The primary components of the library are:

[UltimateSessionHandler](docs/MikeBrant-UltimateSessions-UltimateSessionHandler.md)

A class which extends PHP's `SessionHandler` class with added encryption 
capability. Class instantiation automatically sets php.ini session settings 
to their preferred values and sets the class as session save handler via 
`set_session_save_handler()`.

[AbstractUltimateSessionHandler](docs/MikeBrant-UltimateSessions-AbstractUltimateSessionHandler.md)

An abstract class made available for those wanting to implement their own 
session handler that does not extend from SessionHandler.  This would be an 
atypical situation, but it is here if needed.

TODO - UltimateSessionManager - A class to provide object-oriented access to 
typical PHP session handling functions along with timestamp based session 
security.

Requires:
- PHP 5.6+
- PHP OpenSSL extension (if using encryption)
- [defuse/php-encryption](https://github.com/defuse/php-encryption) library
- PHPUnit 5.7+ (for unit tests only)

This library is developed against PHP 7.1 and tested via Travis CI against:
- PHP 5.6.*
- PHP 7.0.*
- PHP 7.1.*
- PHP Nightly build

[Full Library Documentation](docs/UltimateSession.md)

[Travis CI build status](https://travis-ci.org/mikecbrant/php-ultimate-sessions)

[Code Climate code coverage and health information](https://codeclimate.com/github/mikecbrant/php-ultimate-sessions)

[Packagist page](https://packagist.org/packages/mikecbrant/php-ultimate-sessions)


**Usage**

Usage is intended to be dead simple.  Just instantiate the 
UltimateSessionHandler class, passing optional parameters:
- `$useEncryption` (boolean, default `false`)
- `$encryptionCookiePrefix` (string, default `ULTSESS_`)

and then start your session.

```php

use \MikeBrant\UltimateSessions;

try {
    new UltimateSessionHandler($useEncryption, $encryptionCookiePrefix);
} catch (\Exception $e) {
    // handle as needed (or don't try-catch at all)
}
session_start();
```

Recommended reading related to PHP session security:

[PHP Session Lifecycle](https://gist.github.com/mindplay-dk/623bdd50c1b4c0553cd3)
[PHP.net Session Management Basics](http://php.net/manual/en/features.session.security.management.php)
[PHP.net Securing Session INI Settings](http://php.net/manual/en/session.security.ini.php)
[PHP SessionHandler Class](http://php.net/manual/en/class.sessionhandler.php)