<?php

namespace BoxedCode\Laravel\TwoFactor\Generators;

use BoxedCode\Laravel\TwoFactor\Contracts\TokenGenerator;

class StringTokenGenerator implements TokenGenerator
{
    protected $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function generate()
    {
        return bin2hex(random_bytes(4));
    }
}