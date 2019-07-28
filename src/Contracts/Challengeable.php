<?php

namespace BoxedCode\Laravel\Auth\Challenge\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface Challengeable
{
    /**
     * Get the users default authentication method name.
     * 
     * @return string|bool
     */
    public function getDefaultAuthMethod();

    /**
     * Determine whether the user can authenticate with 
     * the given method.
     * 
     * @param  string $method
     * @return bool
     */
    public function canAuthenticateUsing($method): bool;

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