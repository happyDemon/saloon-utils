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

{% @github-files/github-code-block url="https://github.com/happyDemon/saloon-utils/blob/main/config/saloon-utils.php" visible="true" fullWidth="false" %}

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

Without any other configuration all requests this connector executes will be stored with the [database logger](loggers.md#database-logger).





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

