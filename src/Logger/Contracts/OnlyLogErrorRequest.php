<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Contracts;

/**
 * Implement this interface on requests that should only log non-200 responses
 */
interface OnlyLogErrorRequest {}
