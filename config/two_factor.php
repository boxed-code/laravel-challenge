<?php

use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;
use BoxedCode\Laravel\TwoFactor\NumericTokenGenerator;
use BoxedCode\Laravel\TwoFactor\StringTokenGenerator;

return [

    'user_model' => \App\User::class,

    'tokens' => [
        'table_name' => 'two_factor_tokens',
        'lifetime' => env('TWO_FACTOR_TOKEN_LIFETIME', 3600)
    ],

    'default' => 'email',

    'enabled' => [
        'email'
    ],

    'providers' => [
        'email' => [
            'provider' => 'notification',
            'channels' => ['mail'],
            'token_generator' => StringTokenGenerator::class,
        ],
        'sms' => [
            'provider' => 'notification',
            'channels' => 'twillio_sms',
            'notification' => TwillioAuthenticationRequest::class,
        ],
    ]
    
];