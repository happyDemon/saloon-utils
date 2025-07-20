<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\SaloonUtilsServiceProvider;
use HappyDemon\SaloonUtils\Tests\Saloon\Logger as TestLogger;
use Saloon\Config;
use Saloon\MockConfig;

class TestCase extends \Orchestra\Testbench\TestCase
{
    /**
     * @var Logger
     */
    protected mixed $requestLogger;

    protected function setUp(): void
    {
        parent::setUp();

        Config::preventStrayRequests();
        MockConfig::throwOnMissingFixtures();

        $this->app->bind(Logger::class, TestLogger::class);
        $this->requestLogger = app(Logger::class);
    }

    protected function getPackageProviders($app): array
    {
        return [SaloonUtilsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('settings.db', 'testbench');
    }
}
