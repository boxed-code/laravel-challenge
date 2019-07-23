<?php

use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;
use BoxedCode\Laravel\TwoFactor\NumericTokenGenerator;
use BoxedCode\Laravel\TwoFactor\StringTokenGenerator;

return [

    'models' => [
        'challenge' => \BoxedCode\Laravel\TwoFactor\Models\Challenge::class,
        'enrolment' => \BoxedCode\Laravel\TwoFactor\Models\Enrolment::class,
    ],

    'lifetime' => env('TWO_FACTOR_LIFETIME', 3600),

    'enabled' => [
        'email'
    ],

    'methods' => [
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