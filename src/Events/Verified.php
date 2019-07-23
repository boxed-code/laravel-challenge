<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;

class Verified
{    
    public $challenge;

    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }
}