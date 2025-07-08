# Saloon Utils

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Tests Status][ico-tests]][link-tests]
![Test coverage]([ico-coverage])

Batteries for [Saloon](https://docs.saloon.dev/).

Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
composer require happydemon/saloon-utils
php artisan vendor:publish --tag saloon-utils.config
```

### Request logger

Ensure your [connector](https://docs.saloon.dev/the-basics/connectors) uses the `HappyDemon\SaloonUtils\Logger\LoggerPlugin` trait.

If request logging is enabled, by default, requests will be logged with the `HappyDemon\SaloonUtils\Logger\SaloonRequest` model.

```dotenv
# Disable saloon request logging globally
SALOON_REQUEST_LOGS=false
```

When using the default built-in database logger, you'll have to publish & run migrations:
``` bash
php artisan vendor:publish --tag saloon-utils.migrations
php artisan migrate
```

You can always bind your own `Logger` or overwrite the existing one by binding your implementation to `HappyDemon\SaloonUtils\Logger\Contracts\Logger`.


## Usage



## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

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
[ico-coverage]: https://github.com/happydemon/saloon-utils/blob/main/badge-coverage.svg

[link-packagist]: https://packagist.org/packages/happydemon/saloon-utils
[link-tests]: https://github.com/happyDemon/saloon-utils/actions/workflows/test.yml?query=branch%3Amain
[link-author]: https://github.com/happydemon
[link-contributors]: ../../contributors
