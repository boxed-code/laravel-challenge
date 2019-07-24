<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

interface TokenGenerator
{
    /**
     * Generate a token.
     * 
     * @return string
     */
    public function generate();
}