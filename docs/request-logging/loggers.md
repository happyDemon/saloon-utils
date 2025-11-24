# Loggers

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

## Built in loggers

### Database logger

```
HappyDemon\SaloonUtils\Logger\Stores\DatabaseLogger
```

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
Schedule::command('model:prune', ['--model' => config('saloon-utils.logs.database_model')])->daily();
```

You are able to overwrite the model class altogether by defining your own model in the `saloon-utils.logs.database_model`  config.



This is the default-bundled model:

```php
<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;

/**
 * This model manages request logs
 *
 * @property int $id
 * @property string $connector The fully qualified class name of the connector
 * @property string $request The fully qualified class name of the request
 * @property string $method The HTTP method used
 * @property string $endpoint The endpoint that was called
 * @property array $request_headers The headers sent with the request
 * @property array $request_query The query parameters sent with the request
 * @property array $request_body The body sent with the request
 * @property array $response_headers The headers received in the response
 * @property array $response_body The body received in the response
 * @property int $status_code The HTTP status code received in the response
 * @property Carbon $completed_at When the request was completed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @mixin Builder
 */
class SaloonRequest extends Model
{
    use MassPrunable;

    protected $table = 'saloon_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'connector',
        'request',
        'method',
        'endpoint',
        'request_headers',
        'request_query',
        'request_body',
        'response_headers',
        'response_body',
        'status_code',
        'completed_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setConnection(config('saloon-utils.logs.database_connection', config('database.default')));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'request_headers' => 'array',
            'request_query' => 'array',
            'request_body' => 'array',
            'response_headers' => 'array',
            'response_body' => 'array',
        ];
    }

    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return $this->newQuery()->where(
            'created_at',
            '<=',
            now()->startOfDay()->subDays(config('saloon-utils.logs.keep_for_days', 14))
        );
    }
}

```

### Memory logger

```
HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger
```

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

```php
<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

interface Logger
{
    /**
     * Just before a request is sent
     * Returns log data (null if none can be created)
     */
    public function create(PendingRequest $request, Connector $connector): mixed;

    /**
     * Right after a request was sent.
     *
     * @param  mixed  $log  The log that was returned from $this->create()
     * @return mixed The updated log
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed;

    /**
     * In case there was a fatal error (due to Saloon not being able to connect, for example).
     *
     * @param  mixed  $log  The log that was returned from $this->create()
     * @return mixed The updated log
     */
    public function updateWithFatalError(mixed $log, RequestException $errorResponse, Connector $connector): mixed;

    public function delete(mixed $log, PendingRequest $request): void;
}

```
