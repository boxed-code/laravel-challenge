<?php
use BoxedCode\Laravel\TwoFactor\Generators\StringTokenGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you would like to override the default challenge an enrolment models, 
    | you can do so within the section below.
    */
   
    'models' => [
        'challenge' => \BoxedCode\Laravel\TwoFactor\Models\Challenge::class,
        'enrolment' => \BoxedCode\Laravel\TwoFactor\Models\Enrolment::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Lifetimes
    |--------------------------------------------------------------------------
    |
    | You can configure the lifetimes of various aspects of the package below, 
    | note that all lifetimes are specified in seconds.
    */
   
    'lifetimes' => [

        /*
         | You can require the user to periodically reverify themselves by setting 
         | the value below. By default it is set to zero, this means that the 
         | verification will last for the duration of the session.
         */
        'verification' => env('TWO_FACTOR_VERIFICATION_LIFETIME', 0),

        /*
         | All challenges issued are only valid for a specific length of time, you 
         | can set this here,by default this is set to one hour.
         */
        'challenge' => env('TWO_FACTOR_CHALLENGE_LIFETIME', 60 * 60),

        /*
         | As with challenges, all incomplete enrolments are only valid for specific 
         | length of time, by default this is also set to one hour.
         */
        'enrolment' => env('TWO_FACTOR_ENROLMENT_LIFETIME', 60 * 60),

    ],

    /*
    |--------------------------------------------------------------------------
    | Enabled Authentication Methods
    |--------------------------------------------------------------------------
    |
    | To enable an authentication method you must add its handle to the list below,
    | note that they should be in the order of precedence, the default at the top.
    */
   
    'enabled' => [
        'email'
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Method Configurations
    |--------------------------------------------------------------------------
    |
    | Below are all of the authentication methods setup within the application.
    | We have included some examples and sensible defaults to get you started.
    |
    | Each method will have its own custom configuration options (along with 
    | some default), you should check the methods class for a full list.
    */
   
    'methods' => [

        'email' => [
            'method' => 'notification',
            'channels' => ['mail'],
            'token_generator' => StringTokenGenerator::class,
        ],

        'sms' => [
            'method' => 'notification',
            'channels' => ['\NotificationChannels\Twilio\TwilioChannel'],
            'notification' => \BoxedCode\Laravel\TwoFactor\Notifications\TwilioAuthenticationRequest::class,
        ],

        'authenticator' => [
            'method' => 'google_authenticator',
            'window' => 0,
            'key_size' => 32,
        ]
    ]
    
];