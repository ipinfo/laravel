# CHANGELOG

All notable changes to `ipinfolaravel` will be documented in this file.

## v2.5.0

- Added support for Laravel 10 with latest IPinfo PHP package.

## v2.4.0

- Added support for a custom IP selector function.

## v2.3.0

- Added support for Laravel 9 with latest IPinfo PHP package.

## v2.2.0

- Added the `no_except` config variable which allows suppressing exceptions
  that occur in the IPinfo middleware; the `$request->ipinfo` object will be
  `null` in this case.

## v2.1.3

- The IPinfo PHP SDK will no longer be initialized multiple times - one will be
  initialized at application startup and used throughout.

## v2.1.2

- Use v2.1.1 of PHP SDK (https://github.com/ipinfo/php/releases/tag/v2.1.1).

## v2.1.1

- Fixed https://github.com/ipinfo/laravel/issues/14 with
  https://github.com/ipinfo/laravel/pull/15.

## v2.1.0

- Update to the latest IPinfo PHP package, which supports PHP 8 and deprecates
  PHP 7.2 support.

## Version 2.0

- Supports Laravel 5.x to 8.x.
- The `ipinfo` object on the request object is not accessible via input, e.g.
  `$request->input('ipinfo')->ip`; you must use `$request->ipinfo->ip`, etc.
- The IP for which data is retrieved is now correctly the **client IP**, and
  not the server IP.

## Version 1.0

### Added

- Everything
