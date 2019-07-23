<?php

namespace BoxedCode\Laravel\TwoFactor\Models\Concerns;

trait Helpers
{
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