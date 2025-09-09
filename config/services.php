<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'telegram' => [
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'support_chat_id' => env('TELEGRAM_SUPPORT_CHAT_ID'),
        'waiting_chat_id' => env('TELEGRAM_WAITING_CHAT_ID'), // Group riêng cho hồ sơ đủ điều kiện chờ
        'delayed_notification_minutes' => (int) env('TELEGRAM_DELAYED_NOTIFICATION_MINUTES', 360), // Default 6 hours
    ],

];
