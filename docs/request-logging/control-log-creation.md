# Control log creation

You have full control over when and how your requests are logged.

> [!CAUTION]
> Config file values take precedence over every thing.

## Configuration

On a global level you are able to either disable logging completely or black list requests & connectors:

```php
<?php

declare(strict_types=1);

return [
    'logs' => [
        'enabled' => env('SALOON_REQUEST_LOGS', true),
        // Pruning
        'keep_for_days' => env('SALOON_REQUEST_PRUNE', 14),
        // The bundled migration uses longtext, which allows for 4,294,967,295 characters
        'response_max_length' => 4294967295,
        'database_model' => \HappyDemon\SaloonUtils\Logger\SaloonRequest::class,
        'database_connection' => env('SALOON_REQUEST_DB_CONNECTION', env('DB_CONNECTION')),
        // Skip request logging
        'ignore' => [
            'connectors' => [],
            'requests' => [],
        ],
    ],
];

```

## Requests

Alternatively, you are able to define logging behaviour on requests individually.

#### Log only errors

You can limit logging to only errors by implementing the `OnlyLogErrorRequest` contract.

> [!NOTE]
> You are able to add `HappyDemon\SaloonUtils\Logger\Contracts\OnlyLogErrorRequest` to both `Request` and `Connector` classes.

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

#### Disable logging

You can ensure individual requests will never be recorded by implementing the `DoNotLogRequest` contract.

> [!NOTE]
> You are able to add `HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest` to both `Request` and `Connector` classes.

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

#### Conditional logging

If you want more fine-grained control over which requests should be logged, you can implement the `ConditionallyIgnoreLogs` contract.

> [!NOTE]
> You are able to add `HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs` to both `Request` and `Connector` classes.

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

