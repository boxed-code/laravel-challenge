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
    
    /*public function requiresEnrolmentSetup()
    {
        return true;
    }*/

    /**
     * Perform any pre-setup processing and return any data required by 
     * the user before setup.
     * 
     * @param  Challengeable $user
     * @return array
     */
    public function beforeSetup(Challengeable $user): array
    {
        return [];
    }

    /**
     * Process the provided setup $data and return any additional state data 
     * that will be merged and persisted with the enrolments existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return array               
     */
    public function setup(Challengeable $user, array $state = [], array $data = []): array
    {
        return [];
    }

    /**
     * Perform any actions required to enrol the user into the 
     * authentication method and return any additional state data 
     * that will be merged and persisted with the enrolments
     * existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @return state               
     */
    public function enrol(Challengeable $user, array $state = []): array
    {
        return [];
    }

    /**
     * Perform any actions to disenrol the user from the authentication method.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @return void               
     */
    public function disenrol(Challengeable $user, array $state = [])
    {
        return [];
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

        //$user->notify($notification);

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