<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use Saloon\Http\PendingRequest;

/**
 * A connector or a request can implement this.
 */
interface ConditionallyIgnoreLogs
{
    public function shouldLogRequest(PendingRequest $pendingRequest): bool;
}
