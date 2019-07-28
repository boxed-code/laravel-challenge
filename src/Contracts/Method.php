<?php

namespace BoxedCode\Laravel\Auth\Challenge\Contracts;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;

interface Method
{
    /**
     * Gets whether the method should require a 
     * successful challenge before enrolling the user.
     * 
     * @return bool
     */
    public function requiresEnrolmentChallenge();

    /**
     * Gets whether the method needs to be 
     * setup during enrolment.
     * 
     * @return bool
     */
    public function requiresEnrolmentSetup();

    /**
     * Perform any pre-setup processing and return any data required by 
     * the user before setup.
     * 
     * @param  Challengeable $user
     * @return array
     */
    public function beforeSetup(Challengeable $user): array;

    /**
     * Process the provided setup $data and return any additional state data 
     * that will be merged and persisted with the enrolments existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return array               
     */
    public function setup(Challengeable $user, array $state = [], array $data = []): array;

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
    public function enrol(Challengeable $user, array $state = []): array;

    /**
     * Perform any actions to disenrol the user from the authentication method.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @return void               
     */
    public function disenrol(Challengeable $user, array $state = []);

    /**
     * Dispatch a challenge via the method to the supplied user and return any 
     * additional state data that will be merged and persisted with the 
     * challenges existing state.
     * 
     * @param  Challengeable $user 
     * @param  array         $data 
     * @return array              
     */
    public function challenge(Challengeable $user, array $data = []): array;

    /**
     * Verify the challenge by validating supplied $data and challenge $state, 
     * if it is not valid throw a ChallengeVerificationException. 
     * 
     * If it is valid, return any additional state data that will be merged and 
     * persisted with the challenges existing state.
     * 
     * @param  Challengeable $user  
     * @param  array         $state 
     * @param  array         $data  
     * @return array               
     * @throws ChallengeVerificationException
     */
    public function verify(Challengeable $user, array $state = [], array $data = []): array;
}