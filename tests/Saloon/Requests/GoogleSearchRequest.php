<?php
namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;


use Saloon\Enums\Method;

class GoogleSearchRequest extends \Saloon\Http\Request
{
    protected Method $method = Method::GET;
    public function __construct(protected string $search){}

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
