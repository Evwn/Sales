<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PesaPal Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for PesaPal payment integration.
    | Update these values according to your PesaPal account settings.
    |
    */

    // Environment: 'sandbox' or 'live'
    'environment' => env('PESAPAL_ENVIRONMENT', 'sandbox'),

    // Consumer credentials
    'consumer_key' => env('PESAPAL_CONSUMER_KEY', ''),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET', ''),

    // Business information
    'branch' => env('PESAPAL_BRANCH', 'DEFAULT BRANCH'),
    'email' => env('PESAPAL_EMAIL', 'test@example.com'),
    'first_name' => env('PESAPAL_FIRST_NAME', 'Test'),
    'last_name' => env('PESAPAL_LAST_NAME', 'User'),
    'address' => env('PESAPAL_ADDRESS', 'Test Address'),
    'city' => env('PESAPAL_CITY', 'Nairobi'),

    // Callback settings
    'callback_url' => env('PESAPAL_CALLBACK_URL', null), // Will use route if null

    // Logging
    'log_payments' => env('PESAPAL_LOG_PAYMENTS', true),
    'log_callbacks' => env('PESAPAL_LOG_CALLBACKS', true),
]; 