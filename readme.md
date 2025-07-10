# Saloon Utils

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Tests Status][ico-tests]][link-tests]
![Tests Coverage][ico-coverage]

Batteries for [Saloon](https://docs.saloon.dev/).

Take a look at [contributing.md](contributing.md) to see a to-do list.

## Installation

Via Composer

``` bash
composer require happydemon/saloon-utils
php artisan vendor:publish --tag saloon-utils.config
```

### Request logging

Ensure your [connector](https://docs.saloon.dev/the-basics/connectors) uses the `HappyDemon\SaloonUtils\Logger\LoggerPlugin` trait.

If request logging is enabled, by default, requests will be logged with the `HappyDemon\SaloonUtils\Logger\SaloonRequest` model.

```dotenv
# Disable saloon request logging globally, if not defined, defaults to true
SALOON_REQUEST_LOGS=false
```

When using the default built-in database logger, you'll have to publish & run migrations;

``` bash
php artisan vendor:publish --tag saloon-utils.migrations
php artisan migrate
```

## Request logging

By default, the database logger is used, however, a bundled logger is also available that stores requests in memory, which can be helpful for local debugging and testing. You can always register your own.

Each logger implements `HappyDemon\SaloonUtils\Logger\Contracts\Logger`:
- `HappyDemon\SaloonUtils\Logger\Stores\DatabaseLogger`
- `HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger`

You can set the default logger by binding the `Logger` contract in the service container.

You are also able to define a specific logger [on a `Connector` class](tests/Saloon/Connectors/ConnectorProvidesLogger.php).

Generally, all requests sent by your `Connector` will be logged.

### Ignoring requests

You can ensure individual requests are ignored by implementing `HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest` on the `Request` class.

If you want more fine-grained control over which requests should be logged, you can implement `HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs` on your `Connector` or `Request` class.
This contract allows you to implement your checks through `public function shouldLogRequest(PendingRequest $pendingRequest): bool;`.

### Pruning
@todo command that prunes old request logs

## Changelog

Please refer to the [changelog](changelog.md) for more information on recent changes.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a to-do list.

## Security

If you discover any security-related issues, please email maxim.kerstens@gmail.com instead of using the issue tracker.

## Credits

- [Maxim Kerstens][link-author]
- [All Contributors][link-contributors]

## License

MIT

[ico-version]: https://img.shields.io/packagist/v/happydemon/saloon-utils.svg?style=flat-square
[ico-tests]: https://github.com/happydemon/saloon-utils/actions/workflows/test.yml/badge.svg
[ico-coverage]: https://raw.githubusercontent.com/happyDemon/saloon-utils/refs/heads/main/badge-coverage.svg

[link-packagist]: https://packagist.org/packages/happydemon/saloon-utils
[link-tests]: https://github.com/happyDemon/saloon-utils/actions/workflows/test.yml?query=branch%3Amain
[link-author]: https://github.com/happydemon
[link-contributors]: ../../contributors
