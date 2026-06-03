<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasMultipartBody;

class MultipartBodyRequest extends Request implements HasBody
{
    use HasMultipartBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return 'upload';
    }

    /**
     * @return array<MultipartValue>
     */
    protected function defaultBody(): array
    {
        return [
            new MultipartValue(name: 'field', value: 'value'),
        ];
    }
}
