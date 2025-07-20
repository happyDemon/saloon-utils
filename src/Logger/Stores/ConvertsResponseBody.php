<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Stores;

use Saloon\Http\Response;

trait ConvertsResponseBody
{
    public function convertResponseBody(Response $response): string
    {
        $contentType = $response->header('Content-Type') ?: $response->header('content-type');

        if (in_array($contentType, [
            'application/json',
            'application/xml',
            'application/soap+xml',
            'text/xml',
            'text/html',
            'text/plain',
            null,
        ], true)) {
            $body = $response->body();

            return strlen($body) >= config('saloon-utils.logs.response_max_length') ? 'too large' : $body;
        }

        return 'unsupported body: '.$contentType;
    }
}
