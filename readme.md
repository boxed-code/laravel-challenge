# Multi-method two factor authentication for Laravel apps.
![Build Status](https://travis-ci.com/boxed-code/laravel-challenge.svg?branch=4.x)
[![Latest Stable Version](https://poser.pugx.org/boxed-code/laravel-challenge/v/stable)](https://packagist.org/packages/boxed-code/laravel-challenge)
[![License](https://poser.pugx.org/boxed-code/laravel-challenge/license)](https://packagist.org/packages/boxed-code/laravel-challenge)

![Auth Flow](https://boxedcode.uk/challenge.gif)

## Version Compatibility

 Laravel  | Challengable
:---------|:----------
 5.7.x    | 1.x
 5.8.x    | 2.x
 6.x      | 3.x
 7.x      | 4.x

## Getting Started
A demo project is available at [laravel-challenge-demo](https://github.com/boxed-code/laravel-challenge-demo), see [this commit](https://github.com/boxed-code/laravel-challenge-demo/commit/e7d83c6a719ddafb9412f1aef3285f3bf5a36e55) to view how simple it is to implement.

### Installation
`composer require boxed-code/laravel-challenge` then run the databse migrations using `./artisan migrate`

### Implementation
Modify your User model class to implement `\BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable` and either optionally use the `BoxedCode\Laravel\Auth\Challenge\Challengeable` trait or implement the the methods defined in the contract yourself.

Next you must add the middleware `\BoxedCode\Laravel\Auth\Challenge\Http\Middleware\RequireAuthentication` to the routes you would like to protect or simply add it to the global stack

### Further Steps
Login an enrol yourself to the default 'email' authentication method at `http://localhost/tfa/email/enrol`, then logout and in again to be challenged for 2FA via email.

 ## To Document
 - Overview
 - Configuration options (challengeable.php)
 - Authentication methods
    - Enabling default methods
        - Email
        - Twilio SMS
        - Twilio Voice (WIP)
        - Google Authenticator [OTP]
        - Password (WIP)
    - Custom notification based authentication methods
    - Custom authentication methods
- Challenges
    - Token Generators
    - Lifetimes & Periodic Re-authentication
    - Custom Repositories
 - Authentication for different purposes & lifetimes
 - Skining / Theming views
 - Events

## License
MIT