<?php

namespace BoxedCode\Laravel\TwoFactor;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait Challengeable
{
    public function getDefaultTwoFactorAuthMethod()
    {
        $config = config('two_factor.enabled', []);
        
        $default = array_shift($config);

        $enrolments = $this->enrolments()->enrolled()->get();

        return $enrolments->filter(function($item) use ($default) {
            return $default === $item['method'];
        })->first() ?? $enrolments->first();
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(
            config('two_factor.models.challenge')
        );
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(
            config('two_factor.models.enrolment')
        );
    }
}