<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorFatal;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Config;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;

class PoolTest extends TestCase
{
    /**
     * @throws \Throwable
     */
    #[Test]
    public function logs_each_pooled_response_and_keeps_the_callers_response_handler(): void
    {
        $connector = app(ConnectorGeneric::class);
        $connector->withMockClient(new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]));

        $handledResponses = 0;

        $pool = $connector->loggedPool(
            [new GoogleSearchRequest('saloon'), new GoogleSearchRequest('laravel')],
            responseHandler: function (Response $response) use (&$handledResponses): void {
                $handledResponses++;
            }
        );

        $pool->send()->wait();

        $this->assertCount(
            2,
            $this->requestLogger->getLogs(),
            'Both pooled requests should have been logged.'
        );

        $this->assertSame(
            2,
            $handledResponses,
            'The caller\'s response handler should still fire for every pooled response.'
        );
    }

    /**
     * @throws \Throwable
     */
    #[Test]
    public function logs_a_fatal_pool_error_and_keeps_the_callers_exception_handler(): void
    {
        Config::clearGlobalMiddleware();

        $connector = app(ConnectorFatal::class);

        $caughtException = null;

        $pool = $connector->loggedPool(
            [new GoogleSearchRequest('saloon')],
            exceptionHandler: function (mixed $exception) use (&$caughtException): void {
                $caughtException = $exception;
            }
        );

        $pool->send()->wait();

        $this->assertInstanceOf(
            FatalRequestException::class,
            $caughtException,
            'The caller\'s exception handler should receive the fatal request exception.'
        );

        $logs = $this->requestLogger->getLogs();

        $this->assertCount(1, $logs);
        $this->assertArrayHasKey(
            'error',
            reset($logs),
            'The fatal pool error should have been logged.'
        );
    }
}
