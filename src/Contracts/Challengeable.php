<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Challengeable
{
    public function getDefaultTwoFactorAuthMethod();
    public function canEnrolInTwoFactorAuth($method): bool;
    public function challenges(): HasMany;
    public function enrolments(): HasMany;
}