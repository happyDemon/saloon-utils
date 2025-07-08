<?php

namespace HappyDemon\SaloonUtils\Tests\Saloon\Connectors;

use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class ConnectorGenericThrows extends ConnectorGeneric
{
    use AlwaysThrowOnErrors;
}
