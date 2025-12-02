<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger;

use GuzzleHttp\Promise\PromiseInterface;
use HappyDemon\SaloonUtils\Logger\Middleware\RegisterLoggerMiddleware;
use Illuminate\Support\Traits\ForwardsCalls;
use ReflectionClass;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Connector;
use Saloon\Http\Pool;

/**
 * @mixin Pool
 */
class LoggerPool
{
    use ForwardsCalls;

    protected ReflectionClass $poolReflection;

    public function __construct(
        public readonly Pool $pool
    ) {
        $this->poolReflection = new ReflectionClass($this->pool);
    }

    protected function getProtectedValueFromPool(string $propertyKey): mixed
    {
        $property = $this->poolReflection->getProperty($propertyKey);
        $property->setAccessible(true);

        return $property->getValue($this->pool);
    }

    public function send(): PromiseInterface
    {
        /** @var \Closure(\Saloon\Http\Response, array-key, \GuzzleHttp\Promise\PromiseInterface): (void)|null $responseHandler */
        $responseHandler = $this->getProtectedValueFromPool('responseHandler');

        /** @var Connector|Connector $connector */
        $connector = $this->getProtectedValueFromPool('connector');

        $this->pool->withResponseHandler(
            function (\Saloon\Http\Response $response, mixed $requestId, PromiseInterface $promise) use ($connector, $responseHandler) {
                // If the pending request has log data attached
                if ($logData = $response->getPendingRequest()->config()->get(RegisterLoggerMiddleware::LOGGER_DATA)) {
                    // Log the request
                    $response->getPendingRequest()
                        ->config()
                        ->get(RegisterLoggerMiddleware::CONFIG_LOGGER_SERVICE)
                        ?->logResponse($response, $logData, $connector);
                }

                // Execute original handler
                if (is_callable($responseHandler)) {
                    $responseHandler($response, $requestId, $promise);
                }
            }
        );

        /** @var \Closure(mixed, array-key, \GuzzleHttp\Promise\PromiseInterface): (void)|null $exceptionHandler */
        $exceptionHandler = $this->getProtectedValueFromPool('exceptionHandler');

        $this->pool->withExceptionHandler(
            function (FatalRequestException|RequestException $exception, mixed $requestId, PromiseInterface $promise) use ($connector, $exceptionHandler) {
                // If the pending request has log data attached
                if ($logData = $exception->getPendingRequest()->config()->get(RegisterLoggerMiddleware::LOGGER_DATA)) {
                    // Log the request
                    $exception->getPendingRequest()
                        ->config()
                        ->get(RegisterLoggerMiddleware::CONFIG_LOGGER_SERVICE)
                        ?->logFatalError($exception, $logData, $connector);
                }

                // Execute original exception handler
                if (is_callable($exceptionHandler)) {
                    $exceptionHandler($exception, $requestId, $promise);
                }
            }
        );

        return $this->pool->send();
    }

    /**
     * Forward all calls to the main Pool object
     */
    public function __call(string $name, array $arguments)
    {
        return $this->forwardDecoratedCallTo($this->pool, $name, $arguments);
    }
}
