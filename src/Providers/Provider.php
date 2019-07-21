<?php

namespace BoxedCode\Laravel\TwoFactor\Providers;

use BoxedCode\Laravel\TwoFactor\Challengeable;

interface Provider
{
    public function enroll(Challengeable $user);
    public function challenge(Challengeable $user, $code);
    public function verify(Challengeable $user, $token);
    public function generator();
    public function requiresEnrolmentChallenge();
    public function requiresEnrolmentSetup();
}