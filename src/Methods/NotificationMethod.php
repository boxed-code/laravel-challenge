<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Method as MethodContract;
use BoxedCode\Laravel\TwoFactor\Methods\Method;
use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;

class NotificationMethod extends Method implements MethodContract
{
    public function requiresEnrolmentChallenge()
    {
        return true;
    }

    public function requiresEnrolmentSetup()
    {
        return true;
    }

    public function beforeSetup(Challengeable $user, array $paramters = [])
    {
        return [$user->email];
    }

    public function setup(Challengeable $user, $token = null, array $parameters = [])
    {
        return [$user->name];
    }

    public function enrol(Challengeable $user, $parameters = [])
    {
        //
    }

    public function disenrol(Challengeable $user)
    {
        //
    }

    public function challenge(Challengeable $user, $token)
    {
        $notification = $this->notification(
            $token,
            $this->config['channels'] ?? []
        );

        $user->notify($notification);
    }

    public function verify(Challengeable $user, $token)
    {
        
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