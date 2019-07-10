<?php

return [
    /*
    |--------------------------------------------------------------------------
    | default
    |--------------------------------------------------------------------------
    |
    | The repository to use for establishing a feature's on/off state.
    |
    */

    'default' => 'config',

    /*
    |--------------------------------------------------------------------------
    | Config Feature Switches
    |--------------------------------------------------------------------------
    |
    | This is a set of features to load into the config features repository.
    |
    */

    'feature' => [
        // 'login' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Repositories
    |--------------------------------------------------------------------------
    |
    | Configures the different repository options
    |
    */

    'repositories' => [
        'database' => [
            'table' => 'features',
        ],
        'config' => [
            'key' => 'features.feature',
        ],
        'redis' => [
            'prefix' => 'features',
            'connection' => 'default',
        ],
        'chain' => [
            'drivers' => [
                'config',
                'redis',
                'database',
            ],
            'store' => 'database',
            'update_on_resolve' => true,
        ],
    ],
];
