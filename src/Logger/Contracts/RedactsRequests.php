<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Contracts;

use HappyDemon\SaloonUtils\Logger\Enums\Redactor;

/**
 * You can specify which headers, query params or body data to redact when storing a log.
 */
interface RedactsRequests
{
    /**
     * @see Redactor
     *
     * @return array {
     *               '?headers': ['*'],
     *               '?query': ['api_token'],
     *               '?data': ['data.password'],
     *               }
     */
    public function shouldRedact(): array;
}
