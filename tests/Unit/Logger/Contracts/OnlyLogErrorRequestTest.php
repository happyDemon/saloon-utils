<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Contracts;

use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorOnlyLogErrors;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchErrorRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class OnlyLogErrorRequestTest extends TestCase
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function does_not_log_a_successful_only_error_request(): void
    {
        $connector = app(ConnectorGeneric::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchErrorRequest::class => MockResponse::make('', 200),
        ]));

        $connector->send(new GoogleSearchErrorRequest('saloon'));

        $this->assertCount(
            0,
            $this->requestLogger->getLogs(),
            'A successful response should not be logged for an only-error request.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_a_failed_only_error_request(): void
    {
        $connector = app(ConnectorGeneric::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchErrorRequest::class => MockResponse::make('', 404),
        ]));

        $connector->send(new GoogleSearchErrorRequest('saloon'));

        $this->assertCount(
            1,
            $this->requestLogger->getLogs(),
            'A failed response should be logged for an only-error request.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function does_not_log_a_successful_request_on_an_only_error_connector(): void
    {
        $connector = app(ConnectorOnlyLogErrors::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]));

        $connector->search('saloon');

        $this->assertCount(
            0,
            $this->requestLogger->getLogs(),
            'A successful response should not be logged for an only-error connector.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_a_failed_request_on_an_only_error_connector(): void
    {
        $connector = app(ConnectorOnlyLogErrors::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 404),
        ]));

        $connector->search('saloon');

        $this->assertCount(
            1,
            $this->requestLogger->getLogs(),
            'A failed response should be logged for an only-error connector.'
        );
    }
}
