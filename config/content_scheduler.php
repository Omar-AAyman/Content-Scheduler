<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | This value determines the database connection that will be used for
    | the Content Scheduler application. By default, we use MySQL.
    |
    */
    'connection' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Default Platforms
    |--------------------------------------------------------------------------
    |
    | These are the default platforms that will be seeded into the database
    | when the application is first installed.
    |
    */
    'default_platforms' => [
        [
            'name' => 'Twitter',
            'type' => 'twitter',
            'max_content_length' => 280
        ],
        [
            'name' => 'Instagram',
            'type' => 'instagram',
            'max_content_length' => 2200
        ],
        [
            'name' => 'LinkedIn',
            'type' => 'linkedin',
            'max_content_length' => 3000
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the application's debugging features.
    |
    */
    'debug' => [
        'enabled' => env('APP_DEBUG', false),
        'log_to_console' => true,
        'log_to_backend' => true,
        'log_level' => env('APP_LOG_LEVEL', 'error'), // 'error', 'warning', 'info', 'debug'
    ],
];
