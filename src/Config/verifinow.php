<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | VerifyNow API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for VerifyNow API integration
    |
    */

    'api_key' => env('VERIFINOW_API_KEY', ''),

    'base_url' => env('VERIFINOW_BASE_URL', 'https://7on7-backend.verifinow.io'),

    'webhook_secret' => env('VERIFINOW_WEBHOOK_SECRET', ''),

    'timeout' => (int) env('VERIFINOW_TIMEOUT', 30),

    'register_routes' => env('VERIFINOW_REGISTER_ROUTES', true),

    'queue_verifications' => env('VERIFINOW_QUEUE_VERIFICATIONS', false),

    'cache_verifications' => env('VERIFINOW_CACHE_VERIFICATIONS', true),

    'cache_ttl' => env('VERIFINOW_CACHE_TTL', 3600),

    'retry_failed_verifications' => env('VERIFINOW_RETRY_FAILED', true),

    'max_retries' => env('VERIFINOW_MAX_RETRIES', 3),

    'log_channel' => env('VERIFINOW_LOG_CHANNEL', 'single'),

    'features' => [
        'idv' => env('VERIFINOW_FEATURE_IDV', true),
        'authentication' => env('VERIFINOW_FEATURE_AUTH', true),
        'age_verification' => env('VERIFINOW_FEATURE_AGE_VERIFICATION', true),
    ],
];
