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
        'webhook_secret' => env('ECARTPAY_WEBHOOK_SECRET'),
        /*
         * Comisiones pasarela (MXN). Subtotal = % × monto + fijo; fee final = subtotal × (1 + IVA).
         * Tarjetas nacionales (Visa/Mastercard): 2.9% + $3.70 + IVA
         * Amex nacional: 3.5% + $3.70 + IVA
         * Internacional (Visa/Master/Amex): 3.5% + $3.70 + IVA
         * SPEI: $12.50 + IVA
         */
        'gateway_fees' => [
            'iva_rate' => (float) env('ECARTPAY_GATEWAY_IVA_RATE', 0.16),
            'fixed_mxn' => (float) env('ECARTPAY_GATEWAY_FIXED_MXN', 3.70),
            'national_card_percent' => (float) env('ECARTPAY_GATEWAY_NATIONAL_CARD_PERCENT', 0.029),
            'amex_national_percent' => (float) env('ECARTPAY_GATEWAY_AMEX_NATIONAL_PERCENT', 0.035),
            'international_percent' => (float) env('ECARTPAY_GATEWAY_INTERNATIONAL_PERCENT', 0.035),
            'spei_fixed_mxn' => (float) env('ECARTPAY_GATEWAY_SPEI_FIXED_MXN', 12.50),
        ],
    ],

    /*
    | Motor Go (surge / asignación). En Docker suele ser http://go_worker:8080;
    | en Herd/Valet local, http://127.0.0.1:8080 si el binario escucha ahí.
    */
    'go_worker' => [
        'url' => rtrim(env('GO_WORKER_URL', 'http://127.0.0.1:8080'), '/'),
    ],

    /*
    | Mapbox Directions (driving-traffic): distancia por ruta y duración con tráfico
    | para cotización POS / Tootli Direct. Sin token se usa línea recta (Haversine).
    */
    'mapbox' => [
        'access_token' => env('MAPBOX_ACCESS_TOKEN'),
    ],

];
