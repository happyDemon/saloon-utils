---
icon: circle-wifi
---

# Request logging

Keep track of (all) the requests a connector executes.

Log those requests/responses to your database, keep the logs in-memory or bring your own storage implementation.

## Setup

You can, globally, enable or disable request logging:

```
# If not defined, defaults to true
SALOON_REQUEST_LOGS=false
```

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

#### Globally

If you want to set the default logger you will have to bind an instance in the service container.

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

#### Locally

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

{% hint style="danger" %}
At this moment no records are pruned
{% endhint %}

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

Ensure your custom logger implements the `Logger` interface:

```php
<?php

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

interface Logger
{
    /**
     * Just before a request is sent
     */
    public function create(PendingRequest $request, Connector $connector): mixed;

    /**
     * Right after a request was sent.
     *
     * @param  mixed  $log  The log that was returned from $this->>create())
     * @return mixed The log
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed;

    /**
     * In case there was a fatal error (due to Saloon not being able to connect for example).
     * 
     * @param  mixed  $log  The log that was returned from $this->>create())
     * @return mixed The log
     */
    public function updateWithFatalError(mixed $log, FatalRequestException $errorResponse, Connector $connector): mixed;
}

```

## Ignoring requests

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

If you want more fine-grained control over which requests should be logged, you can implement `ConditionallyIgnoreLogs` on your `Connector` or `Request` class. \
This contract allows you to implement your any logic to prevent a request from being logged by returning `false`.

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
