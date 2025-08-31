<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use Saloon\Http\PendingRequest;

class ConnectorConditionalIgnore extends ConnectorGeneric implements ConditionallyIgnoreLogs
{
    /**
     * Ignore if the search term is "ignore"
     */
    public function shouldLogRequest(PendingRequest $pendingRequest): bool
    {
        return $pendingRequest->query()->get('q') !== 'ignore';
    }
}
