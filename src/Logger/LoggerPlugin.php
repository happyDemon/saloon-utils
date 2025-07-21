<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger;

use HappyDemon\SaloonUtils\Logger\Middleware\RegisterLoggerMiddleware;
use Saloon\Data\Pipe;
use Saloon\Http\Connector;

/**
 * @mixin Connector
 */
trait LoggerPlugin
{
    public function bootLoggerPlugin(): void
    {
        if (config('saloon-utils.logs.enabled') !== true) {
            return;
        }

        $logger = app(LoggerRepository::class);

        // Ensure the logger is only set up once
        if (
            collect($this->middleware()->getRequestPipeline()->getPipes())
                ->filter(fn (Pipe $pipe) => $pipe->name === RegisterLoggerMiddleware::MIDDLEWARE_LOGGER)
                ->isNotEmpty()
        ) {
            return;
        }

        $this->middleware()
            ->onRequest(
                new RegisterLoggerMiddleware($logger, $this),
                RegisterLoggerMiddleware::MIDDLEWARE_LOGGER
            );
    }
}
