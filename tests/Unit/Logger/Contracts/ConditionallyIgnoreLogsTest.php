<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Contracts;

use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorConditionalIgnore;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchConditionalIgnoreRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class ConditionallyIgnoreLogsTest extends TestCase
{
    /**
     * @var ConnectorConditionalIgnore
     */
    protected mixed $connector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connector = app(ConnectorConditionalIgnore::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make(body: '', status: 200),
            GoogleSearchConditionalIgnoreRequest::class => MockResponse::make(body: '', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function respects_contract_on_connector(): void
    {
        // Context: The connector will not log requests that search for "ignore"

        // Verify that the request log was not registered
        $this->connector->search('ignore');
        $this->assertIsArray($this->requestLogger->getLogs());
        $this->assertCount(0, $this->requestLogger->getLogs(), 'Request should not have been registered.');

        // Verify that the request log is registered
        $this->connector->search('saloon');
        $this->assertIsArray($this->requestLogger->getLogs());
        $this->assertCount(1, $this->requestLogger->getLogs(), 'Request should have been registered.');
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function respects_contract_on_request(): void
    {
        // Context: The connector will not log requests that search for "ignore-request"

        // Verify that the request log was not registered
        $this->connector->searchConditionalLog('ignore-request');
        $this->assertIsArray($this->requestLogger->getLogs());
        $this->assertCount(0, $this->requestLogger->getLogs(), 'Request should not have been registered.');

        // Verify that the request log is registered
        $this->connector->searchConditionalLog('saloon');
        $this->assertIsArray($this->requestLogger->getLogs());
        $this->assertCount(1, $this->requestLogger->getLogs(), 'Request should have been registered.');
    }
}
