<?php

declare(strict_types=1);

return [
    'logs' => [
        'enabled' => env('SALOON_REQUEST_LOGS', true),
        // Pruning
        'keep_for_days' => env('SALOON_REQUEST_PRUNE', 14),
        // The bundled migration uses longtext, which allows for 4,294,967,295 characters
        'response_max_length' => 4294967295,
        'database_model' => \HappyDemon\SaloonUtils\Logger\SaloonRequest::class,
        'database_connection' => env('SALOON_REQUEST_DB_CONNECTION', env('DB_CONNECTION')),
        // Skip request logging
        'ignore' => [
            'connectors' => [],
            'requests' => [],
        ],
    ],
];
