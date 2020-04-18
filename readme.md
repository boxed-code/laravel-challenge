# Challengeable
![Build Status](https://travis-ci.com/boxed-code/laravel-challenge.svg?branch=4.x)
[![Latest Stable Version](https://poser.pugx.org/boxed-code/laravel-challenge/v/stable)](https://packagist.org/packages/boxed-code/laravel-challenge)
[![License](https://poser.pugx.org/boxed-code/laravel-challenge/license)](https://packagist.org/packages/boxed-code/laravel-challenge)

Simple, multi-method two factor authentication for laravel.

## Version Compatibility

 Laravel  | Challengable
:---------|:----------
 5.7.x    | 1.x
 5.8.x    | 2.x
 6.x      | 3.x
 7.x      | 4.x

## Getting Started
- Install using `composer require boxed-code/laravel-challenge`
- Modify your User model class to implement `\BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable` and either optionally use the `BoxedCode\Laravel\Auth\Challenge\Challengeable` trait or implement the the methods defined in the contract yourself.
- Add the middleware `\BoxedCode\Laravel\Auth\Challenge\Http\Middleware\RequireAuthentication` to the routes you would like to protect.
- Run migrations `./artisan migrate`
- Enrol yourself via the `./artisan enrol {user_id} email`

## License
MIT