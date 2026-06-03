# Changelog

All notable changes to `saloon-utils` will be documented in this file.

## Version 0.4.2

### Added
- Laravel 13 support

### Changed
- Migrated to Saloon v4 (security release); dropped Saloon v3 support

### Removed
- Dropped Laravel 11 support (no longer receives security fixes)
- Dropped PHP 8.2 support (minimum is now PHP 8.3)


## Version 0.4.1

### Fixed
- `loggedPool` exception handler now accepts `FatalRequestException` (connection failures) alongside `RequestException`


## Version 0.4.0

### Added
- Request pool logging via `loggedPool()`


## Version 0.3.2

### Added
- Support for configuring a custom Eloquent model for database logging (`logs.database_model`)

### Fixed
- Correct bool validation of the `ConditionallyIgnoreLogs` contract on connectors


## Version 0.3.1

### Changed
- Switched middleware registration to invokable classes to prevent memory leaks in queues/octane


## Version 0.3.0

### Added
- Support for request (body, query param, header) redaction
- Added contract so `Connector` or `Request` will only log error responses

## Version 0.2.0

### Added
- configurable request data redaction
- model pruning
- response body content length limit

### Changes
- improved tests


## Version 0.1.0

### Added
- Request logger
