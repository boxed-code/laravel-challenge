<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

interface TokenGenerator
{
    public function generate();
}