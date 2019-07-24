<?php

namespace BoxedCode\Laravel\TwoFactor\Events;

use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment;

class Enrolled
{    
    /**
     * The enrolment instance.
     * 
     * @var Enrolment
     */
    public $enrolment;

    /**
     * Create a new event instance.
     * 
     * @param Enrolment $enrolment
     */
    public function __construct(Enrolment $enrolment)
    {
        $this->enrolment = $enrolment;
    }
}