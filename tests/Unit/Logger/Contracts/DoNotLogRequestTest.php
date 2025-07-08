<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Contracts;

use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequestNoLog;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class DoNotLogRequestTest extends TestCase
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
            GoogleSearchRequestNoLog::class => MockResponse::make(body: '', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function respects_contract(): void
    {
        // Send the request (request implements `DoNotLogRequest`)
        $this->connector->searchWithoutLog('saloon request not logged');

        // Verify that the request log is registered
        $logs = $this->requestLogger->getLogs();

        $this->assertIsArray($logs);
        $this->assertCount(0, $logs, 'Request should have been registered.');
    }
}
