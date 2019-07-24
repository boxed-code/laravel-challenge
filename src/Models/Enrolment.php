<?php

namespace BoxedCode\Laravel\TwoFactor\Models;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment as EnrolmentContract;
use BoxedCode\Laravel\TwoFactor\Methods\MethodNameFormatter;

class Enrolment extends \Illuminate\Database\Eloquent\Model implements EnrolmentContract
{
    use Concerns\Helpers;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor_enrolments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'method',
        'enrolled_at',
        'setup_at',
        'state',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = ['state'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['enrolled_at', 'setup_at'];

    /**
     * Get the friendly name for an enrolment.
     * 
     * @return string
     */
    public function getLabelAttribute()
    {
        return MethodNameFormatter::toLabel(
            $this->method
        );
    }

    /**
     * Enrolment method scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method [description]
     * @return void
     */
    public function scopeMethod($query, $method)
    {
        $query->where('method', '=', $method);
    }

    /**
     * Enrolment enrolled status scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeEnrolled($query, $method = null)
    {
        $query->whereNotNull('enrolled_at');

        if ($method) {
            $query->whereMethod($method);
        }
    }

    /**
     * Enrolment enrolling status scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeEnrolling($query, $method = null)
    {
        $query->whereNull('enrolled_at');

        if ($method) {
            $query->whereMethod($method);
        }
    }

    /**
     * Enrolments ready for GC scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  integer $user_id
     * @param  string  $method   
     * @param  integer $lifetime
     * @return void
     */
    public function scopeReadyForGc($query, $user_id, $method, $lifetime)
    {
        $query->where('user_id', '=', $user_id)
            ->where('method', '=', $method)
            ->whereIsNull('enrolled_at')
            ->where('created_at', '<=', now()->subSeconds($lifetime));
    }
}