<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\RedactsRequests;
use HappyDemon\SaloonUtils\Logger\Enums\Redactor;

class ConnectorRedacts extends ConnectorProvidesLogger implements RedactsRequests
{
    public function shouldRedact(): array
    {
        return [
            Redactor::QUERY->value => ['q'],
        ];
    }
}
