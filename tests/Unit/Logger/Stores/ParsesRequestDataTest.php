<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Stores;

use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorProvidesLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorRedacts;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\MultipartBodyRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\StreamBodyRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class ParsesRequestDataTest extends TestCase
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function normalises_a_multipart_request_body(): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $connector->withMockClient(new MockClient([
            MultipartBodyRequest::class => MockResponse::make('', 200),
        ]));

        $connector->send(new MultipartBodyRequest);

        $this->assertSame(
            'Multipart Body',
            app(MemoryLogger::class)->logs()[0]['request_body']
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function normalises_a_streamed_request_body(): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $connector->withMockClient(new MockClient([
            StreamBodyRequest::class => MockResponse::make('', 200),
        ]));

        $connector->send(new StreamBodyRequest);

        $this->assertSame(
            'Streamed Body',
            app(MemoryLogger::class)->logs()[0]['request_body']
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function marks_an_unsupported_response_content_type(): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchRequest::class => MockResponse::make('binary', 200, ['Content-Type' => 'image/png']),
        ]));

        $connector->search('saloon');

        $this->assertSame(
            'unsupported body: image/png',
            app(MemoryLogger::class)->logs()[0]['response_body']
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function redacts_request_data_configured_on_the_connector(): void
    {
        $connector = app(ConnectorRedacts::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]));

        $connector->search('saloon');

        $this->assertSame(
            'redacted',
            app(MemoryLogger::class)->logs()[0]['request_query']['q']
        );
    }
}
