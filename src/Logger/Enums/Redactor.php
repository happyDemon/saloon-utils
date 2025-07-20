<?php

declare(strict_types=1);

namespace HappyDemon\SaloonUtils\Logger\Enums;

enum Redactor: string
{
    case HEADERS = 'headers';
    case BODY = 'data';
    case QUERY = 'query';
}
