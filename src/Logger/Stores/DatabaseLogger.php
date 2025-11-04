<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Stores;

use Exception;
use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\SaloonRequest;
use Illuminate\Database\Eloquent\Model;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

class DatabaseLogger implements Logger
{
    use ParsesRequestData;

    /**
     * @var class-string<Model|SaloonRequest>
     */
    protected mixed $modelClass;

    public function __construct()
    {
        $this->modelClass = config('saloon-utils.logs.database_model', SaloonRequest::class);
    }

    public function create(PendingRequest $request, Connector $connector): mixed
    {
        $log = $this->modelClass::create([
            'connector' => get_class($connector),
            'request' => get_class($request->getRequest()),
            'method' => $request->getRequest()->getMethod(),
            'endpoint' => $request->getRequest()->resolveEndpoint(),
            'request_headers' => $this->convertsRequestHeaders($request->getRequest()->headers(), $request),
            'request_query' => $this->convertsRequestQueryParameters($request->getRequest()->query(), $request),
            'request_body' => $this->convertsRequestBody($request->body(), $request),
        ]);

        return $log;
    }

    /**
     * @param  SaloonRequest  $log
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed
    {
        $log->update([
            'response_headers' => $response->headers()->all(),
            'response_body' => $this->convertResponseBody($response, $connector),
            'status_code' => $response->status(),
            'completed_at' => now(),
        ]);

        return $log;
    }

    /**
     * @param  SaloonRequest  $log
     */
    public function updateWithFatalError(mixed $log, RequestException | FatalRequestException | SaloonException $errorResponse, Connector $connector): mixed
    {
        $log->update([
            'response_body' => [
                'internal_error' => [
                    'code' => $errorResponse->getCode(),
                    'message' => $errorResponse->getMessage(),
                ],
                'trace' => $errorResponse->getTraceAsString(),
            ],
            'status_code' => 418,
            'completed_at' => now(),
        ]);

        return $log;
    }

    /**
     * @throws Exception
     */
    public function delete(mixed $log, PendingRequest $request): void
    {
        if ($log instanceof $this->modelClass) {
            $log->delete();
        }
    }
}
