<?php

namespace BoxedCode\Laravel\TwoFactor\Providers;

use BoxedCode\Laravel\TwoFactor\NumericTokenGenerator;

abstract class BaseProvider implements Provider
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generator()
    {
        if (isset($this->config['generator'])) {
            return new $this->config['generator']();
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