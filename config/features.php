<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pipeline
    |--------------------------------------------------------------------------
    |
    | The pipeline for the feature to travel through.
    |
    */

    'pipe' => ['model', 'database'],

    /*
    |--------------------------------------------------------------------------
    | Gateways
    |--------------------------------------------------------------------------
    |
    | Configures the different gateway options
    |
    */

    'gateways' => [
        'in_memory' => [
            'driver' => 'in_memory',
            'file' => './features.php',
            'caching' => [
                'store' => 'file',
                'ttl' => 300,
            ],
        ],
        'database' => [
            'driver' => 'database',
            'cache' => [
                'ttl' => 3600,
                'store' => 'file',
            ],
            'filter' => 'system.*',
            'connection' => env('FEATURE_FLAG_DATABASE_CONNECTION'),
            'table' => env('FEATURE_FLAG_DATABASE_TABLE', 'features'),
        ],
        'gate' => [
            'driver' => 'gate',
            'cache' => [
                'ttl' => 3600,
                'per_request' => 1000,
                'store' => null,
            ],
            'gate' => 'feature-flag',
        ],
        'redis' => [
            'driver' => 'redis',
            'prefix' => 'features',
            'connection' => env('FEATURE_FLAG_REDIS_CONNECTION', 'default'),
        ],
    ],
];
