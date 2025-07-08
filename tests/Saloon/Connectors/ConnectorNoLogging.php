<?php

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use Saloon\Http\PendingRequest;

class ConnectorNoLogging extends ConnectorGeneric implements ConditionallyIgnoreLogs
{
    public function shouldLogRequest(PendingRequest $pendingRequest): bool
    {
        return true;
    }
}
