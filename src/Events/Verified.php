<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Challenge;

class Verified
{    
   /**
     * The challenge instance.
     * 
     * @var Challenge
     */
    public $challenge;

    /**
     * Create a new event instance.
     * 
     * @param Challenge $challenge
     */
    public function __construct(Challenge $challenge)
    {
        $this->challenge = $challenge;
    }
}