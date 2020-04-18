<?php

namespace Tests\Integration\Support;

use BoxedCode\Laravel\Auth\Challenge\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable as ChallengeableContract;
use Illuminate\Foundation\Auth\User as BaseUser;

class User extends BaseUser implements ChallengeableContract
{
    use Challengeable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];
}
