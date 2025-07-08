<?php

namespace HappyDemon\SaloonUtils\Logger;

use Saloon\Data\Pipe;
use Saloon\Enums\PipeOrder;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

/**
 * @mixin Connector
 */
trait LoggerPlugin
{
    const string CONFIG_LOGGER_SERVICE = 'saloon.logger.service';
    const string MIDDLEWARE_LOGGER = 'saloon.logger.request';
    const string MIDDLEWARE_RESPONSE = 'saloon.logger.response';
    const string MIDDLEWARE_FATAL = 'saloon.logger.fatal';

    public function bootLoggerPlugin(): void
    {
        if (config('saloon-utils.logs.enabled') !== true) {
            return;
        }

        $logger = app(LoggerService::class);

        // Ensure the logger is only set up once
        if (
            collect($this->middleware()->getRequestPipeline()->getPipes())
                ->filter(fn (Pipe $pipe) => $pipe->name === self::MIDDLEWARE_LOGGER)
                ->isNotEmpty()
        ) {
            return;
        }

        $this->middleware()
            ->onRequest(function (PendingRequest $request) use ($logger) {
                $logger->setUpLogger($this);

                $request->config()->add(static::CONFIG_LOGGER_SERVICE, $logger);
                $loggedRequest = $logger->logRequest($request, $this);

                $request->middleware()
                    ->onResponse(
                        fn (Response $response) => $logger->logResponse($response, $loggedRequest, $this),
                        self::MIDDLEWARE_RESPONSE,
                        PipeOrder::FIRST
                    );
                $request->middleware()
                    ->onFatalException(
                        fn (FatalRequestException $response) => $logger->logFatalError($response, $loggedRequest, $this),
                        self::MIDDLEWARE_FATAL,
                        PipeOrder::FIRST
                    );
            },
                self::MIDDLEWARE_LOGGER
            );
    }
}
