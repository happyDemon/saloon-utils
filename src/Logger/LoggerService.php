<?php

namespace HappyDemon\SaloonUtils\Logger;

use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;
use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\Contracts\ProvidesLogger;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

class LoggerService
{
    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * Called the first time a connector is setting up this service.
     */
    public function setUpLogger(Connector $connector): void
    {
        if (is_a($connector, ProvidesLogger::class)) {
            $this->logger = $connector->setUpRequestLogger();
            return;
        }

        $this->logger = app(Logger::class);
    }

    public function logRequest(PendingRequest $request, Connector $connector): mixed
    {
        $originalRequest = $request->getRequest();

        if (
            // The request log should not be stored
            is_a($originalRequest, DoNotLogRequest::class) ||
            // The request log is optionally stored based on the request
            (
                is_a($originalRequest, ConditionallyIgnoreLogs::class) &&
                /** @var ConditionallyIgnoreLogs $originalRequest */
                $originalRequest->shouldLogRequest($request)
            ) ||
            // The request log is optionally stored based on the connector
            (
                is_a($connector, ConditionallyIgnoreLogs::class) &&
                $connector->shouldLogRequest($request)
            )
        ) {
            return null;
        }

        return $this->logger->create($request, $connector);
    }

    public function logResponse(Response $response, mixed $log, Connector $connector): mixed
    {
        // Initial request was not logged
        if (empty($log)) {
            return null;
        }

        return $this->logger->updateWithResponse($log, $response, $connector);
    }

    /**
     * Saloon cannot connect to an API
     */
    public function logFatalError(FatalRequestException $response, mixed $log, Connector $connector): mixed
    {
        // Initial request was not logged
        if (empty($log)) {
            return null;
        }

        return $this->logger->updateWithFatalError($log, $response, $connector);
    }

    public function logger(): Logger
    {
        return $this->logger;
    }
}
