---
icon: circle-wifi
---

# Request logging

Keep track of (all) the requests a connector executes.

Log those requests/responses to your database, keep the logs in-memory or bring your own storage implementation.

## Configuration

<kbd>.env</kbd> configuration:

```
# If not defined, defaults to true
SALOON_REQUEST_LOGS=false

# If not set, the default database connection will be used
SALOON_REQUEST_DB_CONNECTION=

# If not defined, defaults to 14 (how many days should requests be stored in the db)
SALOON_REQUEST_PRUNE=14
```

In the `saloon-utils.php` config file you can also define which requests or connectors will be ignored. \
\
Any request or connector defined in this list is considered a hard-ignore, checks defined on the request or connector will be bypassed.

{% @github-files/github-code-block url="https://github.com/happyDemon/saloon-utils/blob/main/config/saloon-utils.php" visible="false" fullWidth="false" %}

## Setup

Ensure your [connector](https://docs.saloon.dev/the-basics/connectors) uses the `LoggerPlugin` trait.

```php
<?php

use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use Saloon\Http\Connector;

class ForgeConnector extends Connector
{
    use LoggerPlugin;
    
    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
}
```

Without any other configuration all requests this connector executes will be stored with the [database logger](request-logging.md#database-logger).

## Configuring a logger

### Globally

If you want to replace the default logger you will have to bind an instance in the service container.

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\Stores\DatabaseLogger;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            Logger::class, 
            fn (Application $application) => new DatabaseLogger
        );
    }
}

```

### Locally

If you have a special case for a specific connector, you could define which logger to use on the connector itself:

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\Contracts\ProvidesLogger;
use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;
use Saloon\Http\Connector;

class ForgeConnector extends Connector implements ProvidesLogger
{
    use LoggerPlugin;
    
    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
    
    public static function setUpRequestLogger(): Logger
    {
        return new MemoryLogger
    }
}
```

## Loggers

### Database logger

When using the **default** built-in database logger, you'll have to publish & run migrations;

```bash
php artisan vendor:publish --tag saloon-utils.migrations
php artisan migrate
```

This logger will store each request in the `saloon_requests` table.

{% hint style="info" %}
Be sure to schedule model pruning daily with a cronjob
{% endhint %}

```php
use HappyDemon\SaloonUtils\Logger\SaloonRequest;
Schedule::command('model:prune', ['--model' => SaloonRequest::class])->daily();
```

### Memory logger

This logger can be helpful when debugging or running tests.\
It will setup a cache store under `saloon-utils` with the array driver.

You can retrieve the requests that were sent on the logger itself:

```php
app(MemoryLogger::class)->logs();
(new MemoryLogger)->logs();
```

### Build your own

You can easily build your own logger and set it as the default.

Ensure your custom logger implements the `Logger` interface.

{% hint style="info" %}
Be sure to make use of the `HappyDemon\SaloonUtils\Logger\Stores\ParsesRequestData` trait when implementing your own logger. It provides helper methods for data conversion and redaction.
{% endhint %}

{% @github-files/github-code-block url="https://github.com/happyDemon/saloon-utils/blob/main/src/Logger/Contracts/Logger.php" visible="false" %}

## Ignoring requests

It might be smart to only log requests that were not successful (where the status code is not in 200).

You can do this by adding the `OnlyLogErrorRequest` contract to a `Request` or `Connector`.

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\OnlyLogErrorRequest;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetServersRequest extends Request implements OnlyLogErrorRequest
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/servers';
    }
}
```

You can ensure individual requests are not recorded by implementing `DoNotLogRequest` on the `Request` class.

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;
use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetServersRequest extends Request implements DoNotLogRequest
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/servers';
    }
}
```

If you want more fine-grained control over which requests should be logged, you can implement `ConditionallyIgnoreLogs` on your `Connector` or `Request` class.&#x20;

This contract allows you to implement any logic to prevent a request from being logged by returning `false`.

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use Saloon\Http\Connector;

class ForgeConnector extends Connector implements ConditionallyIgnoreLogs
{
    use LoggerPlugin;
    
    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
    
    public function shouldLogRequest(PendingRequest $pendingRequest): bool
    {
        return true;
    }
}
```

## Redacting request data

There are times you don't want sensitive data logged.

Ensure either your `Request` or `Connector` implements the `RedactsRequests` contract and defines what you want to redact:

```php
<?php

use HappyDemon\SaloonUtils\Logger\Contracts\RedactsRequests;
use HappyDemon\SaloonUtils\Logger\Enums\Redactor;
use HappyDemon\SaloonUtils\Logger\LoggerPlugin;
use Saloon\Http\Connector;

class ForgeConnector extends Connector implements RedactsRequests
{
    use LoggerPlugin;
    
    public function resolveBaseUrl(): string
    {
        return 'https://forge.laravel.com/api/v1';
    }
    
    public function shouldRedact(): array
    {
        return [
            Redactor::HEADERS->value => [
                // redact all
                '*',
            ],
            Redactor::BODY->value => [
                // dot path syntax supported
                'data.password',
            ],
            Redactor::QUERY->value => [
                'api_token',
            ],
        ];
    }
}
```
