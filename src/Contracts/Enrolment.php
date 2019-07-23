<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Enrolment
{
    public function getLabelAttribute();
    public function getKey();
    public function user(): BelongsTo;
    public function scopeMethod($query, $method);
    public function scopeEnrolled($query, $method = null);
    public function scopeEnrolling($query, $method = null);
    public function scopeReadyForGc($query, $user_id, $method, $lifetime);
}