<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Challengeable
{
    /**
     * Get the users default two factor method name.
     * 
     * @return string|bool
     */
    public function getDefaultTwoFactorAuthMethod();

    /**
     * Can the user enrol in the supplied authentication method?
     * 
     * @param  string $method
     * @return bool
     */
    public function canEnrolInTwoFactorAuth($method): bool;

    /**
     * The users challenge relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function challenges(): HasMany;

    /**
     * The users enrolments relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enrolments(): HasMany;
}