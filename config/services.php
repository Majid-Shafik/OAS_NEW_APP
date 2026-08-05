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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'ministry_api' => [
        'url' => env('MINISTRY_API_URL', 'https://portal.test.oasyemen.net/api/high-school-api'),
        'secret' => env('MINISTRY_API_SECRET'),
        'use_local_db' => env('USE_LOCAL_MINISTRY_DB', false),
        'timeout' => (int) env('MINISTRY_API_TIMEOUT', 80),
        'connect_timeout' => (int) env('MINISTRY_API_CONNECT_TIMEOUT', 10),
        'verify_ssl' => env('MINISTRY_API_VERIFY_SSL', true),
    ],

];
