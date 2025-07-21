<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;

interface Logger
{
    /**
     * Just before a request is sent
     * Returns log data (null if none can be created)
     */
    public function create(PendingRequest $request, Connector $connector): mixed;

    /**
     * Right after a request was sent.
     *
     * @param  mixed  $log  The log that was returned from $this->create()
     * @return mixed The updated log
     */
    public function updateWithResponse(mixed $log, Response $response, Connector $connector): mixed;

    /**
     * In case there was a fatal error (due to Saloon not being able to connect, for example).
     *
     * @param  mixed  $log  The log that was returned from $this->create()
     * @return mixed The updated log
     */
    public function updateWithFatalError(mixed $log, FatalRequestException $errorResponse, Connector $connector): mixed;

    public function delete(mixed $log, PendingRequest $request): void;
}
