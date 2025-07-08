<?php

namespace HappyDemon\SaloonUtils;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\DatabaseLogger;
use HappyDemon\SaloonUtils\Logger\LoggerService;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class SaloonUtilsServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     */
    public function boot(): void
    {
        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/saloon-utils.php', 'saloon-utils');

        // Default Logger to bundled database implementation
        $this->app->bind(Logger::class, fn (Application $application) => new DatabaseLogger);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            Logger::class,
            LoggerService::class,
        ];
    }

    /**
     * Console-specific booting.
     */
    protected function bootForConsole(): void
    {
        $this->publishes([
            __DIR__.'/../config/saloon-utils.php' => config_path('saloon-utils.php'),
        ],
            ['saloon-utils.config', 'saloon-utils']
        );
        $this->publishes([
            __DIR__.'/../database/0001_01_01_000001_create_saloon_requests_table.php' => database_path('migrations/0001_01_01_000001_create_saloon_requests_table.php'),
        ],
            ['saloon-utils.migrations', 'saloon-utils']
        );

        // Registering package commands.
        // $this->commands([]);
    }
}
