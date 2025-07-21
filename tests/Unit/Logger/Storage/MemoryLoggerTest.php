<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Storage;

use HappyDemon\SaloonUtils\Logger\Enums\Redactor;
use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorFatal;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorProvidesLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Logger;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRedactRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactBodyAllRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactBodyRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactHeadersAllRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactHeadersRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactQueryAllRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction\RedactQueryRequest;
use HappyDemon\SaloonUtils\Tests\TestCase;
use Illuminate\Cache\Repository;
use Illuminate\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use Saloon\Config;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class MemoryLoggerTest extends TestCase implements StorageLoggerInterface
{
    protected function setUpFreshLoggerAndGetCache()
    {
        $logger = new MemoryLogger;

        $storeProperty = (new ReflectionClass($logger))
            ->getProperty('store');
        $storeProperty->setAccessible(true);

        return $storeProperty->getValue($logger);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    protected function doSuccessfulRequest(?string $body = null): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make($body ?: '', 200),
        ]);
        $connector->withMockClient($mockClient);

        // Send the request
        $connector->search('saloon');
    }

    /**
     * @throws \ReflectionException
     */
    #[Test]
    public function logger_inits_correctly(): void
    {
        $cache = $this->setUpFreshLoggerAndGetCache();

        $this->assertInstanceOf(
            Repository::class,
            $cache,
            'A cache repository should have been initialized.'
        );

        $cache->put('test', 'x');

        $anotherCache = $this->setUpFreshLoggerAndGetCache();

        $this->assertEquals(
            $cache->get('test'),
            $anotherCache->get('test'),
            'Initialising the logger a second time should not reset cache.'
        );
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    #[Test]
    public function logs_response(): void
    {
        $this->doSuccessfulRequest();

        $logger = app(MemoryLogger::class);
        $this->assertCount(1, $logger->logs());

        $log = $logger->logs()[0];
        $request = new GoogleSearchRequest('saloon');

        $this->assertEquals(200, $log['status_code']);
        $this->assertEquals($request->query()->all(), $log['request_query']);
        $this->assertEquals($request->resolveEndpoint(), $log['endpoint']);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    #[Test]
    public function logs_multiple_responses(): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]);
        $connector->withMockClient($mockClient);
        $logger = app(MemoryLogger::class);

        foreach (['saloon', 'saloon v3'] as $i => $search) {
            // Send the request
            $connector->search($search);

            $this->assertCount($i + 1, $logger->logs());

            // verify the data matches
            $request = new GoogleSearchRequest($search);
            $log = $logger->logs()[$i];

            $this->assertEquals(200, $log['status_code'], 'status code should be 200');
            $this->assertEquals($request->query()->all(), $log['request_query'], 'Query parameters should match');
            $this->assertEquals($request->resolveEndpoint(), $log['endpoint'], 'Endpoint should have set correctly');
        }

    }

    #[Test]
    public function handles_fatal_error_correctly(): void
    {
        Config::clearGlobalMiddleware();
        $this->app->bind(Logger::class, fn () => new MemoryLogger);

        /** @var LoggerRepository $repository */
        $repository = app(LoggerRepository::class);
        $connector = app(ConnectorFatal::class);
        $repository->setUpLogger($connector);

        try {
            $connector->search('saloon');
            $this->fail('Should have thrown a FatalRequestException');
        } catch (FatalRequestException $e) {
            $this->assertNull(
                $repository->logFatalError($e, null, $connector),
                'No log should return a null response'
            );

            $log = $repository->logFatalError($e, ['id' => 'request'], $connector);
            $this->assertIsArray($log);
        }
    }

    public static function bodySizes(): array
    {
        return [
            'exceeds limit' => [
                'sent' => '123456789101112',
                'stored' => 'too large',
            ],
            'within limits' => [
                'sent' => 'data',
                'stored' => 'data',
            ],
        ];
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     * @throws InvalidArgumentException
     */
    #[Test]
    #[DataProvider('bodySizes')]
    public function response_body_size_is_respected(string $sent, string $stored): void
    {
        config()->set('saloon-utils.logs.response_max_length', 10);
        $this->doSuccessfulRequest($sent);

        $logs = (new MemoryLogger)->logs();

        $this->assertEquals($stored, $logs[0]['response_body']);
    }

    public static function redacted_provider(): array
    {
        return [
            'header' => [
                'type' => Redactor::HEADERS->value,
                'redact' => RedactHeadersRequest::class,
            ],
            'all headers' => [
                'type' => Redactor::HEADERS->value,
                'redact' => RedactHeadersAllRequest::class,
                'all' => true,
            ],
            'query' => [
                'type' => Redactor::QUERY->value,
                'redact' => RedactQueryRequest::class,
            ],
            'all query parameters' => [
                'type' => Redactor::QUERY->value,
                'redact' => RedactQueryAllRequest::class,
                'all' => true,
            ],
            'body' => [
                'type' => Redactor::BODY->value,
                'redact' => RedactBodyRequest::class,
            ],
            'body all' => [
                'type' => Redactor::BODY->value,
                'redact' => RedactBodyAllRequest::class,
                'all' => true,
            ],
        ];
    }

    #[DataProvider('redacted_provider')]
    #[Test]
    public function redaction_is_applied(string $type, string $redact, bool $all = false): void
    {
        $connector = app(ConnectorProvidesLogger::class);
        $mockClient = new MockClient([
            $redact => MockResponse::make('', 200),
        ]);
        $connector->withMockClient($mockClient);
        $logger = app(MemoryLogger::class);

        /** @var GoogleSearchRedactRequest $request */
        $request = new $redact('saloon');
        $connector->send($request);

        $log = $logger->logs()[0];
        $loggedData = null;
        $data = null;
        $rules = $request->shouldRedact();
        switch ($type) {
            case Redactor::HEADERS->value:
                $loggedData = $log['request_headers'];
                $data = $request->defaultHeaders();
                break;
            case Redactor::QUERY->value:
                $loggedData = $log['request_query'];
                $data = $request->defaultQuery();
                break;
            case Redactor::BODY->value:
                $loggedData = $log['request_body'];
                $data = $request->defaultBody();
                break;
        }

        if ($all) {
            $this->assertEquals(
                'redacted',
                $loggedData,
                'All '.$type.' should have been redacted'
            );

            return;
        }

        foreach ($rules[$type] as $key) {
            $this->assertEquals(
                'redacted',
                Arr::get($loggedData, $key),
                $type.': '.$key.' should have been redacted'
            );
            $this->assertNotEquals(
                Arr::get($data, $key),
                Arr::get($loggedData, $key),
                $type.': '.$key.' should be different to the original value'
            );
        }
    }
}
