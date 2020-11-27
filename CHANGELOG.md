# CHANGELOG

All notable changes to `ipinfolaravel` will be documented in this file.

## Version 2.0

- Supports Laravel 5.x to 8.x.
- The `ipinfo` object on the request object is not accessible via input, e.g.
  `$request->input('ipinfo')->ip`; you must use `$request->ipinfo->ip`, etc.
- The IP for which data is retrieved is now correctly the **client IP**, and
  not the server IP.

## Version 1.0

### Added

- Everything
