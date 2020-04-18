<?php

namespace BoxedCode\Laravel\Auth\Challenge\Events;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challenge;

class Challenged
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
