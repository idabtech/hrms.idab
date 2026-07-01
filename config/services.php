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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'cashfree' => [
        'key' => '',
        'secret' => '',
        'url' => 'https://sandbox.cashfree.com/pg/orders',
    ],

    'sso' => [
        'secret' => env('SSO_SHARED_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | idabcard Integration
    |--------------------------------------------------------------------------
    |
    | IDABCARD_BASE_URL     — Base URL of the idabcard platform (no trailing slash).
    | IDABCARD_API_TIMEOUT  — HTTP timeout in seconds (default 15).
    |
    | The SSO email is resolved from Auth::user()->email at runtime — the
    | authenticated company user's email is the account registered in idabcard.
    |
    */
    'idabcard' => [
        'base_url'    => env('IDABCARD_BASE_URL', ''),
        'api_timeout' => env('IDABCARD_API_TIMEOUT', 15),
    ],
];
