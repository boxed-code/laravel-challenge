<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Challenge
{
    const PURPOSE_ENROLMENT = 'enrolment',
          PURPOSE_AUTH = 'auth';

    public function user(): BelongsTo;
    public function scopePending($query, $method = null);
    public function scopeMethod($query, $method);
    public function scopeReadyForGc($query, $user_id, $method, $lifetime);
}