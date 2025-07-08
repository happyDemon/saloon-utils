<?php
namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\Logger;
use HappyDemon\SaloonUtils\Logger\Contracts\ProvidesLogger;
use HappyDemon\SaloonUtils\Logger\MemoryLogger;

class ConnectorProvidesLogger extends ConnectorGeneric implements ProvidesLogger
{
    public static function setUpRequestLogger(): Logger
    {
        return new MemoryLogger();
    }
}
