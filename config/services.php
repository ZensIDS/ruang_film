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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'rajaongkir' => [
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
        'api_key_shipping_cost' => env('RAJAONGKIR_API_KEY_SHIPPING_COST'),
        'order_base_url' => env('RAJAONGKIR_ORDER_BASE_URL', 'https://api.collaborator.komerce.id/order/api/v1'),
        'api_key_shipping_delivery' => env('RAJAONGKIR_API_KEY_SHIPPING_DELIVERY'),
        'komship_enabled' => filter_var(env('RAJAONGKIR_KOMSHIP_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'legacy_origin_district_id' => env('RAJAONGKIR_ORIGIN_DISTRICT_ID'),
        'fallback_origin_destination_id' => env('RAJAONGKIR_ORIGIN_DESTINATION_ID', env('RAJAONGKIR_ORIGIN_DISTRICT_ID')),
        'default_couriers' => array_values(array_filter(array_map('trim', explode(',', (string) env('RAJAONGKIR_DEFAULT_COURIERS', 'sicepat,jnt,jne,ninja,anteraja,pos,tiki,wahana,lion'))))),
        'timeout' => (int) env('RAJAONGKIR_TIMEOUT', 20),
        'retry_times' => (int) env('RAJAONGKIR_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('RAJAONGKIR_RETRY_SLEEP_MS', 250),
        'origin_destination_id' => env('RAJAONGKIR_ORIGIN_DESTINATION_ID', env('RAJAONGKIR_ORIGIN_DISTRICT_ID')),
        'origin_pin_point' => env('RAJAONGKIR_ORIGIN_PIN_POINT'),
        'shipper' => [
            'brand_name' => env('RAJAONGKIR_SHIPPER_BRAND_NAME'),
            'name' => env('RAJAONGKIR_SHIPPER_NAME'),
            'phone' => env('RAJAONGKIR_SHIPPER_PHONE'),
            'email' => env('RAJAONGKIR_SHIPPER_EMAIL'),
            'address' => env('RAJAONGKIR_SHIPPER_ADDRESS'),
        ],
    ],

];
