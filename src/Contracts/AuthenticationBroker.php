<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

interface AuthenticationBroker
{
    const INVALID_METHOD = 'invalid_method',
          INVALID_TOKEN = 'invalid_token',
          INVALID_CODE = 'invalid_code',
          INVALID_ENROLMENT = 'invalid_enrolment',
          INVALID_CHALLENGE = 'invalid_challenge',
          CODE_VERIFIED = 'code_verified',
          USER_NOT_ENROLLED = 'user_not_enrolled',
          USER_CANNOT_ENROL = 'user_cannot_enrol',
          USER_CHALLENGED = 'user_challenged',
          USER_ENROLLED = 'user_enrolled',
          USER_DISENROLLED = 'user_disenrolled',
          METHOD_REQUIRES_SETUP = 'requires_setup',
          NO_ENROLMENT_IN_PROGRESS = 'no_enrolment_in_progress';

     public function begin(Challengeable $user, $method_name, array $meta = []);
     public function beforeSetup(Challengeable $user, $method_name);
     public function setup(Challengeable $user, $method_name, $token = null, array $meta = []);
     public function enrol(Challengeable $user, $method);
     public function disenrol(Challengeable $user, $method);
     public function challenge(Challengeable $user, $method_name, $purpose, array $meta = []);
     public function verify(Challengeable $user, $method, $token);
     public function canChallenge(Challengeable $user, $method, $purpose);
     public function canEnrol(Challengeable $user, $method);
     public function getEnrolledAuthDriverList(Challengeable $user);
     public function setEventDispatcher(EventDispatcher $events);
     public function getEventDispatcher();
}