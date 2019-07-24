<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;

class Disenrolled
{    
    /**
     * The user instance.
     * 
     * @var Challengeable
     */
    public $user;

    /**
     * The method the user has disenrolled from.
     * 
     * @var string
     */
    public $method;

    /**
     * Create a new event instance.
     * 
     * @param Challengeable $user  
     * @param string        $method
     */
    public function __construct(Challengeable $user, $method)
    {
        $this->user = $user;
        
        $this->method = $method;
    }
}