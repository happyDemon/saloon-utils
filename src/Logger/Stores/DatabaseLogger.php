<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Stores;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\SaloonRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

class DatabaseLogger implements Logger
{
    use ConvertsResponseBody;

    public function create(PendingRequest $request, Connector $connector): mixed
    {
        $log = SaloonRequest::create([
            'connector' => get_class($connector),
            'request' => get_class($request->getRequest()),
            'method' => $request->getRequest()->getMethod(),
            'endpoint' => $request->getRequest()->resolveEndpoint(),
            'request_headers' => $request->getRequest()->headers()->all(),
            'request_query' => $request->getRequest()->query()->all(),
            'request_body' => $request->body()?->all(),
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
            'response_body' => $this->convertResponseBody($response),
            'status_code' => $response->status(),
            'completed_at' => now(),
        ]);

        return $log;
    }

    /**
     * @param  SaloonRequest  $log
     */
    public function updateWithFatalError(mixed $log, FatalRequestException $errorResponse, Connector $connector): mixed
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
}
