<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Storage;

use HappyDemon\SaloonUtils\Logger\SaloonRequest;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorFatal;
use HappyDemon\SaloonUtils\Tests\Saloon\Connectors\ConnectorGeneric;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRequest;
use HappyDemon\SaloonUtils\Tests\TestCaseDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Saloon\Config;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class DatabaseLoggerTest extends TestCaseDatabase implements StorageLoggerInterface
{
    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    protected function doSuccessfulRequest(?string $responseBody = null): void
    {
        $connector = app(ConnectorGeneric::class);
        $mockClient = new MockClient([
            GoogleSearchRequest::class => MockResponse::make($responseBody ?: '', 200),
        ]);
        $connector->withMockClient($mockClient);

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
        $this->doSuccessfulRequest();

        $this->assertDatabaseCount((new SaloonRequest)->getTable(), 1);

        /** @var SaloonRequest $log */
        $log = SaloonRequest::query()->first();
        $request = new GoogleSearchRequest('saloon');

        $this->assertEquals(200, $log->status_code);
        $this->assertEquals(ConnectorGeneric::class, $log->connector);
        $this->assertEquals(GoogleSearchRequest::class, $log->request);
        $this->assertEquals($request->getMethod()->value, $log->method);
        $this->assertEquals($request->query()->all(), $log->request_query);
        $this->assertEquals($request->resolveEndpoint(), $log->endpoint);
    }

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    #[Test]
    public function logs_multiple_responses(): void
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

            /** @var SaloonRequest $log */
            $log = $model->newQuery()->get()[$i];

            $this->assertEquals(
                200,
                $log->status_code,
                'status code should be 200'
            );
            $this->assertEquals(
                $request->query()->all(),
                $log->request_query,
                'Query parameters should match'
            );
            $this->assertEquals(
                $request->resolveEndpoint(),
                $log->endpoint,
                'Endpoint should have set correctly'
            );
        }
    }

    /**
     * @throws RequestException
     */
    #[Test]
    public function handles_fatal_error_correctly(): void
    {
        Config::clearGlobalMiddleware();
        $connector = app(ConnectorFatal::class);

        try {
            // Send the request
            $connector->search('saloon');

            $this->fail('Should have thrown a FatalRequestException');
        } catch (FatalRequestException $e) {
            $this->assertDatabaseCount((new SaloonRequest)->getTable(), 1);

            /** @var SaloonRequest $log */
            $log = SaloonRequest::query()->first();
            $request = new GoogleSearchRequest('saloon');

            $this->assertEquals(418, $log->status_code);
            $this->assertEquals($request->query()->all(), $log->request_query);
            $this->assertEquals($request->resolveEndpoint(), $log->endpoint);
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
     */
    #[Test]
    #[DataProvider('bodySizes')]
    public function response_body_size_is_respected(string $sent, string $stored): void
    {
        config()->set('saloon-utils.logs.response_max_length', 10);
        $this->doSuccessfulRequest($sent);

        /** @var SaloonRequest $log */
        $log = SaloonRequest::query()->first();

        $this->assertEquals($stored, $log->response_body);
    }
}
