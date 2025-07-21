<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests\Redaction;

use HappyDemon\SaloonUtils\Logger\Contracts\RedactsRequests;
use HappyDemon\SaloonUtils\Logger\Enums\Redactor;
use HappyDemon\SaloonUtils\Tests\Saloon\Requests\GoogleSearchRedactRequest;

class RedactQueryAllRequest extends GoogleSearchRedactRequest implements RedactsRequests
{
    public function shouldRedact(): array
    {
        return [
            Redactor::QUERY->value => ['*'],
        ];
    }
}
