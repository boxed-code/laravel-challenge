<?php

namespace BoxedCode\Laravel\TwoFactor\Providers;

use BoxedCode\Laravel\TwoFactor\Challengeable;
use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;
use BoxedCode\Laravel\TwoFactor\Providers\Provider;

class NotificationProvider extends BaseProvider implements Provider
{
    public function requiresEnrolmentChallenge()
    {
        return true;
    }

    public function enroll(Challengeable $user)
    {

    }

    public function challenge(Challengeable $user, $code)
    {
        $notification = $this->notification(
            $code,
            $this->config['channels'] ?? []
        );

        //$user->notify($notification);
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