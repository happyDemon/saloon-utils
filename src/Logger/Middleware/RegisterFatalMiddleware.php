<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Middleware;

use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;

class RegisterFatalMiddleware
{
    public function __construct(
        protected LoggerRepository $logger,
        protected mixed $loggedRequest,
        protected Connector $connector
    ) {}

    public function __invoke(FatalRequestException $response): void
    {
        $this->logger->logFatalError($response, $this->loggedRequest, $this->connector);
    }
}
