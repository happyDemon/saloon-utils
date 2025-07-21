<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Middleware;

use HappyDemon\SaloonUtils\Logger\LoggerRepository;
use Saloon\Http\Connector;
use Saloon\Http\Response;

class RegisterResponseMiddleware
{
    public function __construct(
        protected LoggerRepository $logger,
        protected mixed $loggedRequest,
        protected Connector $connector
    ) {}

    public function __invoke(Response $response): void
    {
        $this->logger->logResponse($response, $this->loggedRequest, $this->connector);
    }
}
