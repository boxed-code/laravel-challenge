<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;

class Disenrolled
{    
    public $user;

    public $method;

    public function __construct(Challengeable $user, $method)
    {
        $this->user = $user;
        
        $this->method = $method;
    }
}