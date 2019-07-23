<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;

interface Method
{
    public function beforeSetup(Challengeable $user): array;
    public function setup(Challengeable $user, array $state = [], array $data = []): array;
    public function enrol(Challengeable $user, array $state = []): array;
    public function disenrol(Challengeable $user, array $state = []);
    public function challenge(Challengeable $user, array $data = []): array;
    public function verify(Challengeable $user, array $state = [], array $data = []): array;
    public function requiresEnrolmentChallenge();
    public function requiresEnrolmentSetup();
}