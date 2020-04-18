<?php

namespace BoxedCode\Laravel\Auth\Challenge\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Challenge
{
    /**
     * Constant representing enrolment as the challenges purpose.
     *
     * @var string
     */
    const PURPOSE_ENROLMENT = 'enrolment';

    /**
     * Constant representing authentication as the challenges purpose.
     */
    const PURPOSE_AUTH = 'auth';

    /**
     * User relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo;

    /**
     * Pending challenges scope.
     *
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param string                                     $method
     * @param string                                     $purpose
     *
     * @return void
     */
    public function scopePending($query, $method = null, $purpose = null);

    /**
     * Challenge method scope.
     *
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param sting                                      $method [description]
     *
     * @return void
     */
    public function scopeMethod($query, $method);

    /**
     * Challenge enrolment scope.
     *
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param sting                                      $method [description]
     *
     * @return void
     */
    public function scopeEnrolment($query, $method);
}
