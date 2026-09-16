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
        'fedapay' => [
        'api_key' => env('FEDAPAY_API_KEY'),
        'account_id' => env('FEDAPAY_ACCOUNT_ID'),
        'mode' => env('FEDAPAY_MODE', 'live'),
        'webhook_url' => env('FEDAPAY_WEBHOOK_URL'),
        'callback_url' => env('FEDAPAY_CALLBACK_URL'),
    ],
        'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
        'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY'),
    ],
    'groq'=>[
        'api_key'=> env('GROQ_API_KEY')
    ],
    'mistral' => [
        'api_key' => env('MISTRAL_API_KEY'),
    ],

    'otp' => [
        'demo_mode' => env('OTP_DEMO_MODE', false),
        'provider' => env('OTP_PROVIDER', 'termii'), // 'termii', 'twilio', 'webhook'
        'webhook_url' => env('OTP_WEBHOOK_URL'),
    ],

    'termii' => [
        'api_key' => env('TERMII_API_KEY'),
        'sender_id' => env('TERMII_SENDER_ID', 'MomoOpti'),
        'url' => env('TERMII_BASE_URL', 'https://api.ng.termii.com'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from_sms' => env('TWILIO_SMS_FROM'),
        'from_whatsapp' => env('TWILIO_WHATSAPP_FROM'),
    ],

];
