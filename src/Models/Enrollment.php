<?php

namespace BoxedCode\Laravel\TwoFactor\Models;

class Enrollment extends \Illuminate\Database\Eloquent\Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor_enrollments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'challengeable_id',
        'provider',
        'token',
    ];

    public function user()
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'challengeable_id'
        );
    }

    protected function getModelForGuard($guard = null)
    {
        if (empty($guard)) {
            $guard = config('auth.default.guards');
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