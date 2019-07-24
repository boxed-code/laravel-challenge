<?php

namespace BoxedCode\Laravel\TwoFactor\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Challenge
{
    /**
     * Constant representing enrolment as the challenges purpose.
     *
     * @var  string
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
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  string $method
     * @return void
     */
    public function scopePending($query, $method = null);

    /**
     * Challenge method scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method [description]
     * @return void
     */
    public function scopeMethod($query, $method);

    /**
     * Challenge enrolment scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method [description]
     * @return void
     */
    public function scopeEnrolment($query, $method);

    /**
     * Challenges ready for GC scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  integer $user_id
     * @param  string  $method   
     * @param  integer $lifetime
     * @return void
     */
    public function scopeReadyForGc($query, $user_id, $method, $lifetime);
}