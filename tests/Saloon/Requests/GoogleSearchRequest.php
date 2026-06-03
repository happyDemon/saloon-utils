<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GoogleSearchRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(protected string $search) {}

    protected function defaultQuery(): array
    {
        return [
            'q' => $this->search,
        ];
    }

    public function resolveEndpoint(): string
    {
        return 'complete/search';
    }
}
