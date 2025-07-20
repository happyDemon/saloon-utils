<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Tests\Unit\Logger\Storage;

interface StorageLoggerInterface
{
    public function logs_response(): void;

    public function logs_multiple_responses(): void;

    public function handles_fatal_error_correctly(): void;

    public function response_body_size_is_respected(string $sent, string $stored): void;
}
