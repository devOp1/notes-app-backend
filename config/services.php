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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],


    'passport' => [
        'login_endpoint' => env('PASSPORT_LOGIN_ENDPOINT', env('APP_URL') . '/oauth/token'),
    ],

    'seeder' => [
        'admin_email'           => env('ADMIN_EMAIL', 'admin@novata.de'),
        'admin_password'        => env('ADMIN_PASSWORD', '%Novata2026)'),
        'default_user_email'    => env('DEFAULT_USER_EMAIL', 'user@novata.de'),
        'default_user_password' => env('DEFAULT_USER_PASSWORD', '%Novata2026)'),
        'passport_client_id'    => env('APP_PASSPORT_CLIENT_ID'),
        'passport_client_secret'=> env('APP_PASSPORT_CLIENT_SECRET'),
    ],

];
