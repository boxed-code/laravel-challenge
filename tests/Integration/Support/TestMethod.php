<?php

namespace Tests\Integration\Support;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Contracts\Method as MethodContract;
use BoxedCode\Laravel\Auth\Challenge\Exceptions\ChallengeVerificationException;
use BoxedCode\Laravel\Auth\Challenge\Methods\Method;

class TestMethod extends Method implements MethodContract
{
    public function requiresEnrolmentChallenge()
    {
        return false;
    }

    public function requiresEnrolmentSetup()
    {
        return false;
    }
    
    public function challenge(Challengeable $user, array $data = []): array
    {
        return ['code' => \Illuminate\Support\Str::random(5)];
    }

    public function verify(Challengeable $user, array $state = [], array $data = []): array
    {
        if ($state['code'] !== $data['code']) {
            throw new ChallengeVerificationException;
        }

        return [];
    }
}