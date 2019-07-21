<?php

namespace BoxedCode\Laravel\TwoFactor;

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