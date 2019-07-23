<?php

namespace BoxedCode\Laravel\TwoFactor\Methods;

use BoxedCode\Laravel\TwoFactor\Generators\NumericTokenGenerator;

abstract class Method
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generator()
    {
        if (isset($this->config['token_generator'])) {
            return new $this->config['token_generator']();
        }

        return new NumericTokenGenerator();
    }

    public function code()
    {
        return $this->generator()->generate();
    }

    public function requiresEnrolmentChallenge()
    {
        return false;
    }

    public function requiresEnrolmentSetup()
    {
        return false;
    }
}