<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Generators\NumericTokenGenerator;

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
     * Get a token generator instance for the method.
     * 
     * @return \BoxedCode\Laravel\TwoFactor\Contracts\TokenGenerator
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