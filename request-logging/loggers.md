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

{% @github-files/github-code-block url="https://github.com/happyDemon/saloon-utils/blob/main/src/Logger/Contracts/Logger.php" visible="true" %}
