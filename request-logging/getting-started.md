---
icon: circle-wifi
---

# Getting started

Keep track of (all) the requests a connector executes.

Log those requests/responses to your database, keep the logs in-memory or bring your own storage implementation.

## Configuration

<kbd>.env</kbd> configuration:

```
# Should any requests be logged? if not defined, defaults to true
SALOON_REQUEST_LOGS=false
```



```
# If not set, the default database connection will be used
SALOON_REQUEST_DB_CONNECTION=

# If not defined, defaults to 14 (how many days should requests be stored in the db)
SALOON_REQUEST_PRUNE=14
```



In the `saloon-utils.php` config file you can also define which requests or connectors will be ignored. \
\
Any requests or connectors defined under `saloon-utils.logs.ignore`  will never be logged, checks defined on the request- or connector-level will be bypassed.

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



## Pools & concurrency

Our  `LoggerPlugin` plugin uses Saloon's middleware to track requests, however, when [pooling requests](https://docs.saloon.dev/digging-deeper/concurrency-and-pools), the response middlewares are not executed.

You can still pool requests with this plugin and have logging applied by creating a logged pool:

```php
<?php

use HappyDemon\SaloonUtils\Logger\LoggerPool;
use Saloon\Http\Pool;

$connector = new ForgeConnector;

/** @var LoggerPool | Pool $pool */
$pool = $connector->loggedPool(
    iterable|callable $requests = [],
    int|callable $concurrency = 5,
    callable|null $responseHandler = null,
    callable|null $exceptionHandler = null
)

$promise = $pool->send();
$promise->wait();
```

The `LoggerPool` can be considered identical to Saloon's `Pool` class.
