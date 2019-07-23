<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;

class Enrolled
{    
    public $enrolment;

    public function __construct(Enrolment $enrolment)
    {
        $this->enrolment = $enrolment;
    }
}