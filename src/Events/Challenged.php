<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Challengeable;

class Challenged
{
    public $user;

    public $token;

    public function __construct(Challengeable $user, $token)
    {
        $this->user = $user;

        $this->token = $token;
    }
}