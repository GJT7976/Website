<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    // Lets the CLI trigger `migrate --force` + `db:seed --force` on this
    // host without SSH or an admin browser session — see
    // DeploySyncController's docblock. Deliberately not set in
    // .env.example: the route 404s (DeploySyncController::__invoke aborts)
    // whenever this is empty, so the endpoint doesn't exist at all until
    // someone deliberately sets DEPLOY_SYNC_TOKEN.
    'deploy_sync' => [
        'token' => env('DEPLOY_SYNC_TOKEN'),
    ],

    // Same pattern, for uploading a release binary without SSH or an admin
    // browser session — see ReleaseSyncController's docblock. Deliberately
    // not set in .env.example: 404s (not a blank-token accept) whenever
    // this is empty, until someone deliberately sets RELEASE_SYNC_TOKEN.
    // A distinct token from DEPLOY_SYNC_TOKEN on purpose — least privilege,
    // since this one can publish a new customer-facing binary.
    'release_sync' => [
        'token' => env('RELEASE_SYNC_TOKEN'),
    ],

];
