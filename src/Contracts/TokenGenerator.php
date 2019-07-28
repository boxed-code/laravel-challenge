<?php

namespace BoxedCode\Laravel\Auth\Challenge\Contracts;

interface TokenGenerator
{
    /**
     * Generate a token.
     * 
     * @return string
     */
    public function generate();
}