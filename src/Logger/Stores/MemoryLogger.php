<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Stores;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\SaloonRequest;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Psr\SimpleCache\InvalidArgumentException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

/**
 * Very simple memory logger that's useful for debugging.
 */
class MemoryLogger implements Logger
{
    use ParsesRequestData;

    protected Repository $store;

    public function __construct()
    {
        if (! Config::has('cache.stores.saloon-utils')) {
            Config::set('cache.stores.saloon-utils', [
                'driver' => 'array',
                'serialize' => false,
            ]);
        }

        $this->store = Cache::store('saloon-utils');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(PendingRequest $request, Connector $connector): mixed
    {
        $requestId = base64_encode(time().mt_rand().'-'.$request->getRequest()->resolveEndpoint());
        $request->config()
            ->add('loggerRequestId', $requestId);

        $data = $this->store->get('requests', []);
        $data[$requestId] = [
            'id' => $requestId,
            'connector' => get_class($connector),
            'request' => get_class($request->getRequest()),
            'method' => $request->getRequest()->getMethod(),
            'endpoint' => $request->getRequest()->resolveEndpoint(),
            'request_headers' => $this->convertsRequestHeaders($request->getRequest()->headers(), $request),
            'request_query' => $this->convertsRequestQueryParameters($request->getRequest()->query(), $request),
            'request_body' => $this->convertsRequestBody($request->body(), $request),
            'sent_at' => now(),
        ];
        $this->store->set('requests', $data);

        return $data[$requestId];
    }

    /**
     * @param  SaloonRequest  $log
     *
     * @throws InvalidArgumentException
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed
    {
        $log = array_merge($log, [
            'response_headers' => $response->headers()->all(),
            'response_body' => $this->convertResponseBody($response, $connector),
            'status_code' => $response->status(),
            'completed_at' => now(),
        ]);

        $this->store->set('requests', array_merge($this->store->get('requests'), [$log['id'] => $log]));

        return $log;
    }

    /**
     * @param  SaloonRequest  $log
     *
     * @throws InvalidArgumentException
     */
    public function updateWithFatalError(mixed $log, FatalRequestException $errorResponse, Connector $connector): mixed
    {
        $log = array_merge($log, [
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

        $this->store->set('requests', array_merge($this->store->get('requests'), [$log['id'] => $log]));

        return $log;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function logs(): array
    {
        return array_values($this->store->get('requests', []));
    }
}
