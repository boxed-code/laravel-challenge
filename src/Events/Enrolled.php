<?php

namespace BoxedCode\Laravel\Auth\Challenge\Events;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Enrolment;

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