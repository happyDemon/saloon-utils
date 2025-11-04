<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon;

use Illuminate\Support\Facades\Cache;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

/**
 * Quick and dirty logger that stores requests in the array cache.
 */
class Logger implements \HappyDemon\SaloonUtils\Logger\Contracts\Logger
{
    public function create(PendingRequest $request, Connector $connector): mixed
    {
        $log = [
            'request' => [
                'method' => $request->getRequest()->getMethod(),
                'endpoint' => $request->getRequest()->resolveEndpoint(),
                'query' => $request->getRequest()->query(),
            ],
            'headers' => $request->headers(),
            'body' => $request->body(),
            'url' => $request->getUrl(),
        ];

        $this->registerInCache($this->cacheKey($request), $log);

        return $log;
    }

    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed
    {
        $log['response'] = [
            'headers' => $response->headers(),
            'body' => $response->body(),
        ];

        $this->registerInCache($this->cacheKey($response->getPendingRequest()), $log);

        return $log;
    }

    public function updateWithFatalError(mixed $log, RequestException | FatalRequestException | SaloonException $errorResponse, Connector $connector): mixed
    {
        $log['error'] = [
            'code' => $errorResponse->getCode(),
            'message' => $errorResponse->getMessage(),
        ];

        $this->registerInCache($this->cacheKey($errorResponse->getPendingRequest()), $log);

        return $log;
    }

    public function delete(mixed $log, PendingRequest $request): void
    {
        $existing = Cache::driver('array')->get('saloon.logs', []);
        unset($existing[$this->cacheKey($request)]);

        Cache::driver('array')->put('saloon.logs', $existing);
    }

    /* ************************************************************************************************************** */

    protected function cacheKey(PendingRequest $pendingRequest): string
    {
        return $this->buildKey($pendingRequest->getRequest()->resolveEndpoint(), $pendingRequest->query()->all());
    }

    public function buildKey($endPoint, array $queryParameters = []): string
    {
        return $endPoint.'.'.base64_encode(http_build_query($queryParameters));
    }

    protected function registerInCache($key, $data): void
    {
        $existing = Cache::driver('array')->get('saloon.logs', []);
        Cache::driver('array')->put('saloon.logs', array_merge($existing, [$key => $data]));
    }

    public function getLogs(): array
    {
        return Cache::driver('array')->get('saloon.logs', []);
    }
}
