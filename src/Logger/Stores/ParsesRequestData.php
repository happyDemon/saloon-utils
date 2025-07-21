<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Stores;

use HappyDemon\SaloonUtils\Logger\Contracts\RedactsRequests;
use HappyDemon\SaloonUtils\Logger\Enums\Redactor;
use Illuminate\Support\Arr;
use Saloon\Contracts\ArrayStore;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Repositories\Body\StreamBodyRepository;

trait ParsesRequestData
{
    protected array $supportedContentTypes = [
        'application/json',
        'application/xml',
        'application/soap+xml',
        'text/xml',
        'text/html',
        'text/plain',
        null,
    ];

    protected function convertsRequestBody(?BodyRepository $body, PendingRequest $request): mixed
    {
        $normalized = match (true) {
            default => null,
            $body instanceof StreamBodyRepository => 'Streamed Body',
            $body instanceof MultipartBodyRepository => 'Multipart Body',
        };

        if ($normalized !== null) {
            return $normalized;
        }

        $data = $body?->all();

        if (empty($data)) {
            return null;
        }

        return $this->redact($data, Redactor::BODY, $request->getRequest(), $request->getConnector());
    }

    protected function convertsRequestHeaders(?ArrayStore $headers, PendingRequest $request): mixed
    {
        $data = $headers?->all();

        if (empty($data)) {
            return null;
        }

        return $this->redact($data, Redactor::HEADERS, $request->getRequest(), $request->getConnector());
    }

    protected function convertsRequestQueryParameters(?ArrayStore $queryParameters, PendingRequest $request): mixed
    {
        $data = $queryParameters?->all();

        if (empty($data)) {
            return null;
        }

        return $this->redact($data, Redactor::QUERY, $request->getRequest(), $request->getConnector());
    }

    protected function redact($data, Redactor $type, Request $request, Connector $connector): mixed
    {
        if (is_a($request, RedactsRequests::class)) {
            $redact = $request->shouldRedact();

            if (isset($redact[$type->value])) {
                return $this->redactDataFromPayload($data, $redact[$type->value]);
            }
        }

        if (is_a($connector, RedactsRequests::class)) {
            $redact = $connector->shouldRedact();

            if (isset($redact[$type->value])) {
                return $this->redactDataFromPayload($data, $redact[$type->value]);
            }
        }

        return $data;
    }

    protected function redactDataFromPayload($payload, array $redactKeys): mixed
    {
        if (in_array('*', $redactKeys, true)) {
            return 'redacted';
        }

        if (is_array($payload)) {
            foreach ($redactKeys as $key) {
                if (Arr::has($payload, $key)) {
                    Arr::set($payload, $key, 'redacted');
                }
            }
        }

        return $payload;
    }

    protected function convertResponseBody(?Response $response, Connector $connector): ?string
    {
        if ($response === null) {
            return null;
        }

        $contentType = $response->header('Content-Type') ?: $response->header('content-type');

        if (in_array($contentType, $this->supportedContentTypes, true)) {
            $body = $response->body();

            return strlen($body) >= config('saloon-utils.logs.response_max_length') ? 'too large' : $body;
        }

        return 'unsupported body: '.$contentType;
    }
}
