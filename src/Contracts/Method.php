<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;

interface Method
{
    public function beforeSetup(Challengeable $user, array $paramters = []);
    public function setup(Challengeable $user, $token = null, array $parameters = []);
    public function enrol(Challengeable $user);
    public function disenrol(Challengeable $user);
    public function challenge(Challengeable $user, $code);
    public function verify(Challengeable $user, $token);
    public function requiresEnrolmentChallenge();
    public function requiresEnrolmentSetup();
    //public function generateChallengeToken(): string;
}