<?php

declare(strict_types=1);

return [
    'logs' => [
        'enabled' => env('SALOON_REQUEST_LOGS', true),
        // Pruning
        'keep_for_days' => env('SALOON_REQUEST_PRUNE', 14),
        // Skip request logging
        'ignore' => [
            'connectors' => [],
            'requests' => [],
        ],
    ],
];
