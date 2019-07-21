<?php

namespace BoxedCode\Laravel\TwoFactor;

interface TokenGenerator
{
    public function generate();
}