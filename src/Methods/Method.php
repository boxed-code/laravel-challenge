<?php

namespace BoxedCode\Laravel\Auth\Challenge\Methods;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Challengeable;
use BoxedCode\Laravel\Auth\Challenge\Exceptions\ChallengeVerificationException;
use BoxedCode\Laravel\Auth\Challenge\Generators\NumericTokenGenerator;

abstract class Method
{
    /**
     * Configuration name.
     * 
     * @var string
     */
    protected $name;

    /**
     * Configuration.
     * 
     * @var array
     */
    protected $config;

    /**
     * Create a new method instance.
     *
     * @param sting $name
     * @param array $config
     */
    public function __construct($name, array $config)
    {
        $this->name = $name;

        $this->config = $config;
    }

    /**
     * Get the methods display label.
     * 
     * @return string
     */
    public function getDisplayLabel()
    {
        return $this->config['label'];
    }

    /**
     * Gets whether the method should require a 
     * successful challenge before enrolling the user.
     * 
     * @return bool
     */
    public function requiresEnrolmentChallenge()
    {
        return false;
    }

    /**
     * Gets whether the method needs to be 
     * setup during enrolment.
     * 
     * @return bool
     */
    public function requiresEnrolmentSetup()
    {
        return false;
    }

    /**
     * Perform any pre-setup processing and return any data required by 
     * the user before setup.
     * 
     * @param  Challengeable $user
     * @return array
     */
    public function beforeSetup(Challengeable $user): array
    {
        return [$state = [], $data = []];
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
        return [];
    }

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
     * @return aray               
     */
    public function verify(Challengeable $user, array $state = [], array $data = []): array
    {
        throw new ChallengeVerificationException;
    }

    /**
     * Get a token generator instance for the method.
     * 
     * @return \BoxedCode\Laravel\Auth\Challenge\Contracts\TokenGenerator
     */
    public function generator()
    {
        if (isset($this->config['token_generator'])) {
            return new $this->config['token_generator']();
        }

        return new NumericTokenGenerator();
    }

    /**
     * Generate a new token.
     * 
     * @return string
     */
    public function token()
    {
        return $this->generator()->generate();
    }
}