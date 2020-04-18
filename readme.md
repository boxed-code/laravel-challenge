# Challengeable
A package to simplify two factor / multi-factor authentication for laravel.

## Version Compatibility

 Laravel  | Challengable
:---------|:----------
 5.7.x    | dev-master

## Getting Started
- `composer require boxed-code/laravel-challenge`
- Modify your user class to implement `\BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable` and either optionally use the `BoxedCode\Laravel\Auth\Challenge\Challengeable` trait or implement the the methods defined in the contract yourself.
- Run migrations `./artisan migrate`