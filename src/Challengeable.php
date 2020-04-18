<?php

namespace BoxedCode\Laravel\Auth\Challenge;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait Challengeable
{
    /**
     * Get the users default authentication method name.
     * 
     * @return string|bool
     */
    public function getDefaultAuthMethod()
    {
        $manager = app('auth.challenge.broker')->getMethodManager();

        $default = $manager->getDefaultMethod();

        $enrolments = $this->enrolments()->enrolled()->get();

        $enrolment = $enrolments->filter(function($item) use ($default) {
            return $default === $item['method'];
        })->first() ?? $enrolments->first();

        return $enrolment ? $enrolment['method'] : false;
    }

    /**
     * Determine whether the user can authenticate with 
     * the given method.
     * 
     * @param  string $method
     * @return bool
     */
    public function canAuthenticateUsing($method): bool
    {
        return true;
    }

    /**
     * The users challenge relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(
            config('challenge.models.challenge'),
            'user_id'
        );
    }

    /**
     * The users enrolments relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function enrolments(): HasMany
    {
        return $this->hasMany(
            config('challenge.models.enrolment'),
            'user_id'
        );
    }
}