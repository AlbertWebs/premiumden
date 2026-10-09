<?php

return [
    'payment' => [
        'driver' => env('PAYMENT_DRIVER', 'disabled'),
        'callback_secret' => env('PAYMENT_CALLBACK_SECRET'),
        'daraja' => [
            'environment' => env('DARAJA_ENVIRONMENT', 'sandbox'),
            'consumer_key' => env('DARAJA_CONSUMER_KEY'),
            'consumer_secret' => env('DARAJA_CONSUMER_SECRET'),
            'shortcode' => env('DARAJA_SHORTCODE'),
            'passkey' => env('DARAJA_PASSKEY'),
            'transaction_type' => env('DARAJA_TRANSACTION_TYPE', 'CustomerPayBillOnline'),
            'callback_ips' => array_filter(explode(',', (string) env('DARAJA_CALLBACK_IPS', '196.201.214.200,196.201.214.206,196.201.213.114,196.201.214.207,196.201.214.208,196.201.213.44,196.201.212.127,196.201.212.138,196.201.212.129,196.201.212.136,196.201.212.74,196.201.212.69'))),
        ],
    ],
    'contact' => ['to_address' => env('CONTACT_TO_ADDRESS')],
];
