<?php

namespace BoxedCode\Laravel\Auth\Challenge\Generators;

use BoxedCode\Laravel\Auth\Challenge\Contracts\TokenGenerator;

class NumericTokenGenerator implements TokenGenerator
{
    /**
     * Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new generator instance.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Generate a token.
     *
     * @return string
     */
    public function generate()
    {
        return random_int(
            $this->config['min'] ?? 10000,
            $this->config['max'] ?? 99999
        );
    }
}
