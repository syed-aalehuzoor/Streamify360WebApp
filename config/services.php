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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
    
    'azure' => [
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'tenant_id' => env('AZURE_TENANT_ID'),
        'client_id' => env('AZURE_CLIENT_ID'),
        'storage_key' => env('AZURE_STORAGE_KEY'),
        'storage_name' => env('AZURE_STORAGE_NAME'),
        'storage_container' => env('AZURE_STORAGE_CONTAINER'),
    ],

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
    'cloudflare' => [
        'email' => env('CLOUDFLARE_EMAIL'),
        'api_key' => env('CLOUDFLARE_API_KEY'),
        'account_id' => env('CLOUDFLARE_ACCOUNT_ID'),
    ],
    'sendgrid' => [
        'key' => env('SENDGRID_API_KEY'),
    ],
    'recaptcha' => [
        'secret' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
