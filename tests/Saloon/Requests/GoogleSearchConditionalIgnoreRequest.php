<?php
namespace HappyDemon\SaloonUtils\Tests\Saloon\Requests;


use HappyDemon\SaloonUtils\Logger\Contracts\ConditionallyIgnoreLogs;
use Saloon\Http\PendingRequest;

class GoogleSearchConditionalIgnoreRequest extends GoogleSearchRequest implements ConditionallyIgnoreLogs
{
    public function shouldLogRequest(PendingRequest $pendingRequest): bool
    {
        return $pendingRequest->query()->get('q') === 'ignore-request';
    }
}
