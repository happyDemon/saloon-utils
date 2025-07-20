<?php

declare(strict_types=1);

return [
    'logs' => [
        'enabled' => env('SALOON_REQUEST_LOGS', true),
        'ignore' => [
            'connectors' => [],
            'requests' => [],
        ],
    ],
];
