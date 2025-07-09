<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class PluginTest extends TestCase
{
    /**
     * @var ConnectorGeneric
     */
    protected mixed $connector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connector = app(ConnectorGeneric::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make(body: '', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function gets_set_up_correctly(): void
    {
        // Send the request
        $response = $this->connector->search('saloon');

        // Ensure the middleware was registered
        $this->assertCount(
            1,
            $this->connector->middleware()->getRequestPipeline()->getPipes(),
            'Logger middleware should have been registered.'
        );

        $logService = $response->getPendingRequest()->config()->get(ConnectorGeneric::CONFIG_LOGGER_SERVICE);
        $this->assertNotNull(
            $logService,
            'Logger service should have been registered in the pending request\'s config.'
        );

        $this->assertSame(
            LoggerRepository::class,
            get_class($logService),
            'Configured logger service should be the correct class'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function respects_globally_disabled(): void
    {
        Config::set('saloon-utils.logs.enabled', false);

        $this->connector = app(ConnectorGeneric::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make(body: '', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);

        // Send the request
        $this->connector->search('saloon');

        // Ensure the middleware was registered
        $this->assertCount(
            0,
            $this->connector->middleware()->getRequestPipeline()->getPipes(),
            'Logger middleware should not have been registered since logging is disabled globally.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_normal_request_correctly(): void
    {
        // Send the request
        $this->connector->search('saloon');

        // Verify that the request log is registered
        $logs = $this->requestLogger->getLogs();

        $this->assertIsArray($logs);
        $this->assertCount(1, $logs, 'Request should have been registered.');
        $this->assertArrayHasKey(
            $this->requestLogger->buildKey('complete/search', ['q' => 'saloon']),
            $logs,
            'Request should have been logged.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function middle_ware_is_registered_once(): void
    {
        // Context: he logger is registered in middleware

        // Send the request
        $this->connector->search('saloon');

        // Send another request
        $this->connector->search('saloon v3');

        $this->assertCount(
            1,
            $this->connector->middleware()->getRequestPipeline()->getPipes(),
            'Logger middleware should have been registered only once.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function each_request_is_logged(): void
    {
        // Send the request
        $this->connector->search('saloon');

        // Send another request
        $this->connector->search('saloon v3');

        // Verify that the request logs are handled correctly
        $logs = $this->requestLogger->getLogs();

        $this->assertIsArray($logs);
        $this->assertCount(2, $logs, 'Request should have been registered.');

        $this->assertArrayHasKey(
            $this->requestLogger->buildKey('complete/search', ['q' => 'saloon']),
            $logs,
            'Initial request should have been logged.'
        );
        $this->assertArrayHasKey(
            $this->requestLogger->buildKey('complete/search', ['q' => 'saloon v3']),
            $logs,
            'Second request should have been logged.'
        );
    }
}
