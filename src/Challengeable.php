<?php

namespace BoxedCode\Laravel\TwoFactor;

interface Challengeable
{
    public function canEnrollInTwoFactorAuth($provider_name);
}