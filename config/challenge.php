<?php
use BoxedCode\Laravel\Auth\Challenge\Generators\StringTokenGenerator;

return [
    /*
    |--------------------------------------------------------------------------
    | Enforcing Status
    |--------------------------------------------------------------------------
    |
    | You can select to enable two factor authentication for all users or only 
    | those who are enrolled in one or more method.
    |
    | 'all'    -   All users, whether enrolled or not, effectively locking 
    |              non-enrolled users out.
    | 'enrolled' - Only users that are enrolled in one or more authentication.
    |
    */
   
    'enforce' => env('CHALLENGE_ENFORCING', 'enrolled'),

    /*
    |--------------------------------------------------------------------------
    | Models
    |--------------------------------------------------------------------------
    |
    | If you would like to override the default challenge an enrolment models, 
    | you can do so within the section below.
    */
   
    'models' => [
        'challenge' => \BoxedCode\Laravel\Auth\Challenge\Models\Challenge::class,
        'enrolment' => \BoxedCode\Laravel\Auth\Challenge\Models\Enrolment::class,
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
        'verification' => env('CHALLENGE_VERIFICATION_LIFETIME', 0),

        /*
         | All challenges issued are only valid for a specific length of time, you 
         | can set this here,by default this is set to one hour.
         */
        'challenge' => env('CHALLENGE_REQUEST_LIFETIME', 60 * 60),

        /*
         | As with challenges, all incomplete enrolments are only valid for specific 
         | length of time, by default this is also set to one hour.
         */
        'enrolment' => env('CHALLENGE_ENROLMENT_LIFETIME', 60 * 60),

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
            'label' => 'E-mail',
            'method' => 'notification',
            'channels' => ['mail'],
            'token_generator' => StringTokenGenerator::class,
        ],

        'sms' => [
            'label' => 'SMS',
            'method' => 'notification',
            'channels' => ['\NotificationChannels\Twilio\TwilioChannel'],
            'notification' => \BoxedCode\Laravel\Auth\Challenge\Notifications\TwilioAuthenticationRequest::class,
        ],

        'authenticator' => [
            'label' => 'Google Authenticator',
            'method' => 'google_authenticator',
            'window' => 1,
            'key_size' => 32,
            //'qr_generator' => 'bacon-v1',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Automatic Route Binding
    |--------------------------------------------------------------------------
    |
    | By default, the package automatically binds the required routes to a 
    | default HTTP controller. You can switch off automatic registration or 
    | change the controller name using the options below.
    */
   
   'routing' => [

        /**
         * Automatically register the default routes to the controller specified below?
         * (Routes can also be bound be calling Route::challenge())
         */
        'register' => true,

        /**
         * Where should we direct the default authentication routes?
         */
        'controller' => \BoxedCode\Laravel\Auth\Challenge\Http\AuthController::class,

   ]
    
    
];