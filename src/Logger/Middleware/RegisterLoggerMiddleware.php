<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Middleware;

use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use Saloon\Enums\PipeOrder;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;

class RegisterLoggerMiddleware
{
    const LOGGER_DATA = 'saloon.logger.log';

    const CONFIG_LOGGER_SERVICE = 'saloon.logger.service';

    const MIDDLEWARE_LOGGER = 'saloon.logger.request';

    const MIDDLEWARE_RESPONSE = 'saloon.logger.response';

    const MIDDLEWARE_FATAL = 'saloon.logger.fatal';

    public function __construct(
        protected LoggerRepository $logger,
        protected Connector $connector
    ) {}

    public function __invoke(PendingRequest $request): void
    {
        $this->logger->setUpLogger($this->connector);

        $request->config()->add(self::CONFIG_LOGGER_SERVICE, $this->logger);
        $loggedRequest = $this->logger->logRequest($request, $this->connector);
        $request->config()->add(self::LOGGER_DATA, $loggedRequest);

        $request->middleware()
            ->onResponse(
                new RegisterResponseMiddleware($this->logger, $loggedRequest, $this->connector),
                self::MIDDLEWARE_RESPONSE,
                PipeOrder::FIRST
            );
        $request->middleware()
            ->onFatalException(
                new RegisterFatalMiddleware($this->logger, $loggedRequest, $this->connector),
                self::MIDDLEWARE_FATAL,
                PipeOrder::FIRST
            );
    }
}
