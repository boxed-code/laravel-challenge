<?php

namespace BoxedCode\Laravel\Auth\Challenge\Models\Concerns;

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
            app('auth.challenge')->getModelForGuard(),
            'user_id'
        );
    }
}
