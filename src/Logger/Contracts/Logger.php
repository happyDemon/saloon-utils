<?php

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

interface Logger
{
    public function create(PendingRequest $request, Connector $connector): mixed;

    /**
     * @param mixed $log The log that was returned from $this->>create())
     *
     * @return mixed The log
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed;

    /**
     * @param mixed $log The log that was returned from $this->>create())
     *
     * @return mixed The log
     */
    public function updateWithFatalError(mixed $log, FatalRequestException $errorResponse, Connector $connector): mixed;
}
