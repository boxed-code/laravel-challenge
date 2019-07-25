<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Method as MethodContract;
use BoxedCode\Laravel\TwoFactor\Exceptions\TwoFactorVerificationException;
use BoxedCode\Laravel\TwoFactor\Methods\Method;
use BoxedCode\Laravel\TwoFactor\Notifications\DefaultAuthenticationRequest;

class NotificationMethod extends Method implements MethodContract
{
    /**
     * Gets whether the method should require a 
     * successful challenge before enrolling the user.
     * 
     * @return bool
     */
    public function requiresEnrolmentChallenge()
    {
        return true;
    }

    /**
     * Dispatch a challenge via the method to the supplied user and return any 
     * additional state data that will be merged and persisted with the 
     * challenges existing state.
     * 
     * @param  Challengeable $user 
     * @param  array         $data 
     * @return array              
     */
    public function challenge(Challengeable $user, array $data = []): array
    {
        $notification = $this->notification($token = $this->token());

        $user->notify($notification);

        return ['token' => $token];
    }

    /**
     * Verify the challenge by validating supplied $data and challenge $state, 
     * if it is not valid throw a TwoFactorVerificationException. 
     * 
     * If it is valid, return any additional state data that will be merged and 
     * persisted with the challenges existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return aray               
     */
    public function verify(Challengeable $user, array $state = [], array $data = []): array
    {
        if (strval($state['token']) === strval($data['code'])) {
            return [];
        }

        throw new TwoFactorVerificationException;
    }

    /**
     * Set the notification to the user.
     * 
     * @param  string $token
     * @return \Illuminate\Notifications\Notification
     */
    protected function notification($token)
    {
        // Use the configured notification if 
        // one is available.
        if (isset($this->config['notification'])) {
            return new $this->config['notification'](
                $token,
                $this->config['channels']
            );
        }

        return new DefaultAuthenticationRequest(
            $token,
            $this->config['channels']
        );
    }
}