<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use HappyDemon\SaloonUtils\Logger\Contracts\OnlyLogErrorRequest;
use Saloon\Enums\Method;

class GoogleSearchErrorRequest extends \Saloon\Http\Request implements OnlyLogErrorRequest
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
