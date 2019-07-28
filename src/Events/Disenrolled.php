<?php

namespace BoxedCode\Laravel\Auth\Challenge\Events;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Enrolment;

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