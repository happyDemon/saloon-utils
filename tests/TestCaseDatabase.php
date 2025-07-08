<?php

namespace Shyfter\Settings\Tests;

namespace HappyDemon\SaloonUtils\Tests;

use HappyDemon\SaloonUtils\Logger\DatabaseLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Logger;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TestCaseDatabase extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('settings.db', 'testbench');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database');

        $this->artisan(
            'migrate',
            ['--database' => 'testbench']
        )
            ->run();

        // Set the logger to the default one
        $this->app->bind(Logger::class, DatabaseLogger::class);
        $this->requestLogger = app(Logger::class);
    }
}
