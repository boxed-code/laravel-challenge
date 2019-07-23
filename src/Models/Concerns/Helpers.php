<?php

namespace BoxedCode\Laravel\TwoFactor\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait Helpers
{
    public function getStateAttribute()
    {
        if (!empty($this->attributes['state'])) {
            return decrypt($this->attributes['state']);
        }

        return [];
    }

    public function setStateAttribute($value)
    {
        if (!empty($value)) {
            $value = encrypt($value);
        }

        $this->attributes['state'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'user_id'
        );
    }

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