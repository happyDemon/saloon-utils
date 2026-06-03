<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use HappyDemon\SaloonUtils\Logger\Contracts\OnlyLogErrorRequest;

class ConnectorOnlyLogErrors extends ConnectorGeneric implements OnlyLogErrorRequest {}
