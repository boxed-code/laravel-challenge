<?php

namespace BoxedCode\Laravel\TwoFactor\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Helpers
{
    /**
     * Decrypt and get the state attribute.
     * 
     * @return array
     */
    public function getStateAttribute()
    {
        if (!empty($this->attributes['state'])) {
            return decrypt($this->attributes['state']);
        }

        return [];
    }

    /**
     * Encrypt and set the state attribute.
     * 
     * @param array $value
     */
    public function setStateAttribute($value)
    {
        if (!empty($value)) {
            $this->attributes['state'] = encrypt($value);
            return;
        }

        $this->attributes['state'] = null;
    }

    /**
     * Get the user the model belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'user_id'
        );
    }

    /**
     * Get the model associate with an authentication guard.
     * 
     * @param  string|null $guard
     * @return string
     */
    protected function getModelForGuard($guard = null)
    {
        if (empty($guard)) {
            $guard = config('auth.defaults.guard');
        }

        return collect(config('auth.guards'))
            ->map(function ($guard) {
                if (! isset($guard['provider'])) {
                    return;
                }

                return config("auth.providers.{$guard['provider']}.model");
            })->get($guard);
    }
}