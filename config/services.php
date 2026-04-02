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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'firebase' => [
        'server_key' => env('FIREBASE_SERVER_KEY'),
    ],

    'ecartpay' => [
        'public_key'  => env('ECARTPAY_PUBLIC_KEY'),
        'private_key' => env('ECARTPAY_PRIVATE_KEY'),
        'base_url'    => env('ECARTPAY_BASE_URL', 'https://sandbox.ecartpay.com'),
        'bank_transfer_method_id' => env('ECARTPAY_BANK_TRANSFER_METHOD_ID', '66c397dafd263a538b8312a1'),
    ],

];
