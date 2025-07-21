<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;
use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorConditionalIgnore;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorFatal;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Logger;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchConditionalIgnoreRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequestNoLog;
use HappyDemon\SaloonUtils\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Config;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class LoggerRepositoryTest extends TestCase
{
    protected function mockResponse(Connector $connector, MockResponse $mockResponse, ?string $request = null): Connector
    {
        $mockClient = new MockClient([
            $request ?: GoogleSearchRequest::class => $mockResponse,
        ]);
        $connector->withMockClient($mockClient);

        return $connector;
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_request(): void
    {
        $this->mock(
            LoggerRepository::class,
            function (MockInterface $mock) {
                $mock->makePartial();
                $mock->shouldReceive('setUpLogger')->once();
                $mock->shouldReceive('logRequest')->once();
            }
        );

        $connector = app(ConnectorGeneric::class);
        $this->mockResponse($connector, MockResponse::make('', 200));

        // Send the request
        $connector->search('saloon');
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_response(): void
    {
        $this->mock(
            LoggerRepository::class,
            function (MockInterface $mock) {
                $mock->makePartial();
                $mock->shouldReceive('logResponse')->once();
            }
        );

        $connector = app(ConnectorGeneric::class);
        $this->mockResponse($connector, MockResponse::make('', 200));

        // Send the request
        $connector->search('saloon');
    }

    #[Test]
    public function logs_fatal_error(): void
    {
        // Clear the global middleware, we want to make a real request
        Config::clearGlobalMiddleware();

        $this->mock(
            LoggerRepository::class,
            function (MockInterface $mock) {
                $mock->makePartial();
                $mock->shouldReceive('logFatalError')->once();
            }
        );
        $this->expectException(FatalRequestException::class);

        $connector = app(ConnectorFatal::class);

        // Send the request
        $connector->search('saloon');
    }

    public static function data_provider(): array
    {
        return [
            'generic request' => [
                'type' => 'request',
                'contract' => null,
                'connector' => ConnectorGeneric::class,
                'request' => GoogleSearchRequest::class,
            ],
            'ignore request' => [
                'type' => 'request',
                'contract' => DoNotLogRequest::class,
                'connector' => ConnectorGeneric::class,
                'request' => GoogleSearchRequestNoLog::class,
            ],
            'conditionally ignore request' => [
                'type' => 'request',
                'contract' => ConditionallyIgnoreLogs::class,
                'connector' => ConnectorGeneric::class,
                'request' => GoogleSearchConditionalIgnoreRequest::class,
            ],
            'generic connector' => [
                'type' => 'connector',
                'contract' => null,
                'connector' => ConnectorGeneric::class,
                'request' => GoogleSearchRequest::class,
            ],
            'conditionally ignore request from connector' => [
                'type' => 'connector',
                'contract' => ConditionallyIgnoreLogs::class,
                'connector' => ConnectorConditionalIgnore::class,
                'request' => GoogleSearchRequest::class,
            ],
        ];
    }

    /**
     * @param  string  $type  connector|request
     * @param  string|null  $contract  Just for context
     * @param  class-string  $connector
     * @param  class-string  $request
     *
     * @throws BindingResolutionException
     * @throws FatalRequestException
     * @throws RequestException
     * @throws \ReflectionException
     */
    #[Test]
    #[DataProvider('data_provider')]
    public function no_logs_if_configured(string $type, ?string $contract, string $connector, string $request): void
    {
        // Reset config
        config()->set('saloon-utils.logs.ignore.requests', []);
        config()->set('saloon-utils.logs.ignore.connectors', []);

        // configure what needs to be ignored
        switch ($type) {
            case 'request':
                config()->set('saloon-utils.logs.ignore.requests', [$request]);
                break;
            case 'connector':
                config()->set('saloon-utils.logs.ignore.connectors', [$connector]);
                break;
        }

        $this->app->bind(
            Logger::class,
            fn () => new MemoryLogger
        );

        /** @var ConnectorGeneric $mock */
        $mock = (new $connector)
            ->withMockClient(new MockClient([
                '*' => MockResponse::make('', 200),
            ]));

        // Make a request
        $mock->send(new $request('saloon'));

        $this->assertCount(
            0,
            $this->app->make(Logger::class)->logs(),
            'No request should have been logged.'
        );
    }


    public static function data_provider_ignore_connector(): array
    {
        return [
            'ignore success' => [
                'logs' => false,
                'response' => MockResponse::make('', 200),
            ],
            'log error' => [
                'logs' => true,
                'response' => MockResponse::make('', 404),
            ],
        ];
    }

    #[DataProvider('data_provider_ignore_connector')]
    #[Test]
    public function log_requests_based_on_error_response(bool $logs, MockResponse $response): void
    {
        $this->app->bind(
            Logger::class,
            fn () => new MemoryLogger
        );

        /** @var ConnectorGeneric $mock */
        $mock = (new ConnectorGeneric)
            ->withMockClient(new MockClient([
                '*' => $response,
            ]));

        $mock->search('saloon');

        $logs = (new MemoryLogger)->logs();

        $this->assertCount($logs ? 1 : 0, $logs);
    }
}
