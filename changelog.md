# Changelog

All notable changes to `saloon-utils` will be documented in this file.

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
