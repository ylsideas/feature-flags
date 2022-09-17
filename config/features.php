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
            'file' => env('FEATURE_FLAG_IN_MEMORY_FILE', '.features.php'),
            'driver' => 'in_memory',
            'caching' => [
                'ttl' => 300,
            ],
        ],
        'database' => [
            'driver' => 'database',
            'cache' => [
                'ttl' => 600,
            ],
            'connection' => env('FEATURE_FLAG_DATABASE_CONNECTION'),
            'table' => env('FEATURE_FLAG_DATABASE_TABLE', 'features'),
        ],
        'gate' => [
            'driver' => 'gate',
            'gate' => env('FEATURE_FLAG_GATE_GATE', 'feature'),
            'cache' => [
                'ttl' => 600,
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'prefix' => env('FEATURE_FLAG_REDIS_PREFIX', 'features'),
            'connection' => env('FEATURE_FLAG_REDIS_CONNECTION', 'default'),
        ],
    ],
];
