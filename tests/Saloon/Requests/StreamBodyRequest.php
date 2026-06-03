<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasStreamBody;

class StreamBodyRequest extends Request implements HasBody
{
    use HasStreamBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return 'upload';
    }

    /**
     * @return resource
     */
    protected function defaultBody(): mixed
    {
        return fopen('php://memory', 'r+b');
    }
}
