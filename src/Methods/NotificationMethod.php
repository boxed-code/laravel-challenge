<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Method as MethodContract;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorVerificationException;
use BoxedCode\Laravel\TwoFactor\Methods\Method;
use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;

class NotificationMethod extends Method implements MethodContract
{
    public function requiresEnrolmentChallenge()
    {
        return true;
    }

    public function beforeSetup(Challengeable $user): array
    {
        return [];
    }

    public function setup(Challengeable $user, array $state = [], array $data = []): array
    {
        return [];
    }

    public function enrol(Challengeable $user, array $state = []): array
    {
        return [];
    }

    public function disenrol(Challengeable $user, array $state = [])
    {
        return [];
    }

    public function challenge(Challengeable $user, array $data = []): array
    {
        $notification = $this->notification(
            $token = $this->code()
        );

        $user->notify($notification);

        return ['token' => $token];
    }

    public function verify(Challengeable $user, array $state = [], array $data = []): array
    {
        if (strval($state['token']) === strval($data['code'])) {
            return [
                'verify' => $data
            ];
        }

        throw new TwoFactorVerificationException;
    }

    protected function notification($code)
    {
        if (isset($this->config['notification'])) {
            return new $this->config['notification'](
                $code,
                $this->config['channels']
            );
        }

        return new DefaultAuthenticationRequest(
            $code,
            $this->config['channels']
        );
    }
}