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

    'pipe' => ['in_memory', 'database'],

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
            'caching' => [
                'ttl' => 300,
            ],
        ],
        'database' => [
            'driver' => 'database',
            'cache' => [
                'ttl' => 3600,
            ],
            'connection' => env('FEATURE_FLAG_DATABASE_CONNECTION'),
            'table' => env('FEATURE_FLAG_DATABASE_TABLE', 'features'),
        ],
        'gate' => [
            'driver' => 'gate',
            'gate' => 'feature-flag',
            'cache' => [
                'ttl' => 3600,
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'prefix' => 'features',
            'connection' => env('FEATURE_FLAG_REDIS_CONNECTION', 'default'),
        ],
    ],
];
