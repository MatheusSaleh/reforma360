<?php

declare(strict_types=1);

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
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Calculadoras da Reforma Tributária (Receita Federal)
    |--------------------------------------------------------------------------
    |
    | Serviços internos (containers sem acesso público) consumidos pelos
    | clients em App\Integrations\Calculadora.
    |
    */

    'calculadora_regular' => [
        'url'             => env('CALCULADORA_REGIME_REGULAR_URL', 'http://calculadora-regular:8080/api'),
        'timeout'         => (int) env('CALCULADORA_TIMEOUT', 15),
        'connect_timeout' => (int) env('CALCULADORA_CONNECT_TIMEOUT', 3),
        'retries'         => (int) env('CALCULADORA_RETRIES', 3),
        'retry_sleep_ms'  => (int) env('CALCULADORA_RETRY_SLEEP_MS', 200),
    ],

    'calculadora_simples' => [
        'url' => env('CALCULADORA_SIMPLES_URL', 'http://calculadora-simples:8080/api'),
    ],

    'danfse' => [
        'url'     => env('DANFSE_URL', 'http://danfse:8080/api'),
        'timeout' => (int) env('DANFSE_TIMEOUT', 20),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
