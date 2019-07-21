<?php

namespace BoxedCode\Laravel\TwoFactor;

class NumericTokenGenerator implements TokenGenerator
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function generate()
    {
        return random_int(
            $this->config['min'] ?? 10000, 
            $this->config['max'] ?? 99999
        );
    }
}