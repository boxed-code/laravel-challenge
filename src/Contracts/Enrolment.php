<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Enrolment
{
    /**
     * Get the friendly name for an enrolment.
     * 
     * @return string
     */
    public function getLabelAttribute();

    /**
     * Get the enrolments storage key.
     * 
     * @return mixed
     */
    public function getKey();

    /**
     * Get the user the model belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Enrolment method scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeMethod($query, $method);

    /**
     * Enrolment enrolled status scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeEnrolled($query, $method = null);

    /**
     * Enrolment enrolling status scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeEnrolling($query, $method = null);
}