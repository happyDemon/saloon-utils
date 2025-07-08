<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Logger\LoggerService;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

#[CoversClass(LoggerService::class)]
class LoggerServiceTest extends TestCase
{
    protected function mockResponse(Connector $connector, MockResponse $mockResponse): void
    {
        $mockClient = new MockClient([
            GoogleSearchRequest::class => $mockResponse,
        ]);
        $connector->withMockClient($mockClient);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_request(): void
    {
        $this->mock(
            LoggerService::class,
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
            LoggerService::class,
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
    public function logs_fatal_exception(): void
    {
        $this->markTestSkipped('Needs a proper implementation that triggers FatalRequestException.');
    }
}
