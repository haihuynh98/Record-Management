<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Notifications
    |--------------------------------------------------------------------------
    |
    | Enable database notifications to be displayed in the admin panel.
    |
    */

    'database' => [
        'enabled' => true,
        'polling_interval' => '30s',
        'trigger' => 'notifications.database-notifications-trigger',
    ],

    /*
    |--------------------------------------------------------------------------
    | Broadcast Notifications
    |--------------------------------------------------------------------------
    |
    | Enable broadcast notifications to be displayed in the admin panel.
    |
    */

    'broadcast' => [
        'enabled' => false,
        'echo' => [
            'broadcaster' => env('BROADCAST_DRIVER', 'pusher'),
            'key' => env('VITE_PUSHER_APP_KEY'),
            'cluster' => env('VITE_PUSHER_APP_CLUSTER'),
            'force_tls' => env('VITE_PUSHER_FORCE_TLS', false),
        ],
    ],
];
