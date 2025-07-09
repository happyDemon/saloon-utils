<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorProvidesLogger;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCaseDatabase;
use Illuminate\Cache\Repository;
use PHPUnit\Framework\Attributes\Test;
use Psr\SimpleCache\InvalidArgumentException;
use ReflectionClass;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class MemoryLoggerTest extends TestCaseDatabase
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
        $connector = app(ConnectorProvidesLogger::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]);
        $connector->withMockClient($mockClient);

        // Send the request
        $connector->search('saloon');

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
    public function logs_multiple(): void
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
}
