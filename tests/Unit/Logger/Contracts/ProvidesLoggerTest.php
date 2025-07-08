<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Contracts;


use HappyDemon\SaloonUtils\Logger\Contracts\ProvidesLogger;
use HappyDemon\SaloonUtils\Logger\MemoryLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorProvidesLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;


#[CoversClass(ProvidesLogger::class)]
class ProvidesLoggerTest extends TestCase
{
    /**
     * @var ConnectorGeneric
     */
    protected mixed $connector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connector = app(ConnectorProvidesLogger::class);
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
    public function respectsContract(): void
    {
        // Send the request (request implements `DoNotLogRequest`)
        $response = $this->connector->search('saloon request not logged');

        // Verify that the request log is registered
        $logs = $this->requestLogger->getLogs();

        // The logger assigned in the base test case should not contain any logs
        $this->assertIsArray($logs);
        $this->assertEmpty($logs);

        /** @var MemoryLogger $logger */
        $logger = $response->getPendingRequest()->config()->get(ConnectorProvidesLogger::CONFIG_LOGGER_SERVICE)->logger();
        $this->assertIsArray($logger->logs());
        $this->assertCount(1, $logger->logs());
    }
}
