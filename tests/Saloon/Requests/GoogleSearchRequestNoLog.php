<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;

class GoogleSearchRequestNoLog extends GoogleSearchRequest implements DoNotLogRequest {}
