<?php

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use HappyDemon\SaloonUtils\Logger\Contracts\DoNotLogRequest;

class GoogleSearchRequestNoLog extends GoogleSearchRequest implements DoNotLogRequest {}
