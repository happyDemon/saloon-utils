<?php

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger;

use HappyDemon\SaloonUtils\Logger\SaloonRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCaseDatabase;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class DatabaseLoggerTest extends TestCaseDatabase
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_response(): void
    {
        $connector = app(ConnectorGeneric::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]);
        $connector->withMockClient($mockClient);

        // Send the request
        $connector->search('saloon');

        $this->assertDatabaseCount((new SaloonRequest)->getTable(), 1);

        $log = SaloonRequest::first();
        $request = new GoogleSearchRequest('saloon');

        $this->assertEquals(200, $log->status_code);
        $this->assertEquals($request->query()->all(), $log->request_query);
        $this->assertEquals($request->resolveEndpoint(), $log->endpoint);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_multiple(): void
    {
        $connector = app(ConnectorGeneric::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make('', 200),
        ]);
        $connector->withMockClient($mockClient);

        foreach (['saloon', 'saloon v3'] as $i => $search) {
            // Send the request
            $connector->search($search);

            $model = new SaloonRequest;
            $this->assertDatabaseCount($model->getTable(), $i + 1);

            // verify the data matches
            $request = new GoogleSearchRequest($search);
            $log = $model->get()[$i];

            $this->assertEquals(200, $log->status_code, 'status code should be 200');
            $this->assertEquals($request->query()->all(), $log->request_query, 'Query parameters should match');
            $this->assertEquals($request->resolveEndpoint(), $log->endpoint, 'Endpoint should have set correctly');
        }
    }
}
