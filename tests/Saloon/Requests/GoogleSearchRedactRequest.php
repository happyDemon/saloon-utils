<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;

use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Traits\Body\HasJsonBody;

class GoogleSearchRedactRequest extends \Saloon\Http\Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::GET;

    public function __construct(protected string $search) {}

    public function defaultQuery(): array
    {
        return [
            'q' => $this->search,
            'secret' => 'could be redacted',
            'redact' => [
                'id' => 'should be redacted',
            ],
        ];
    }

    public function defaultHeaders(): array
    {
        return [
            'Authorization' => 'should be redacted',
            'Custom' => 'possibly be redacted',
            'Saloon' => 'possibly be redacted',
        ];
    }

    public function resolveEndpoint(): string
    {
        return 'complete/search';
    }

    public function defaultBody(): array
    {
        return [
            'auth' => [
                'username' => 'should be logged',
                'password' => 'should be redacted',
            ],
            'redact' => 'should be redacted',
        ];
    }
}
