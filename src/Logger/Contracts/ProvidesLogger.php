<?php

namespace HappyDemon\SaloonUtils\Logger\Contracts;

/**
 * You can provide a request logger directly from the connector.
 */
interface ProvidesLogger
{
    public static function setUpRequestLogger(): Logger;
}
