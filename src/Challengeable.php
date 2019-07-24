<?php

namespace BoxedCode\Laravel\TwoFactor;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait Challengeable
{
    /**
     * Get the users default two factor method name.
     * 
     * @return string|bool
     */
    public function getDefaultTwoFactorAuthMethod()
    {
        $manager = app('auth.tfa.broker')->getMethodManager();

        $default = $manager->getDefaultMethod();

        $enrolments = $this->enrolments()->enrolled()->get();

        $enrolment = $enrolments->filter(function($item) use ($default) {
            return $default === $item['method'];
        })->first() ?? $enrolments->first();

        return $enrolment ? $enrolment['method'] : false;
    }

    /**
     * The users challenge relationship.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(
            config('two_factor.models.challenge')
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
            config('two_factor.models.enrolment')
        );
    }
}