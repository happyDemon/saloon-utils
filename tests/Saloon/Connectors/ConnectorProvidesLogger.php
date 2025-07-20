<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\Contracts\ProvidesLogger;
use HappyDemon\SaloonUtils\Logger\Stores\MemoryLogger;

class ConnectorProvidesLogger extends ConnectorGeneric implements ProvidesLogger
{
    public static function setUpRequestLogger(): Logger
    {
        return new MemoryLogger;
    }
}
