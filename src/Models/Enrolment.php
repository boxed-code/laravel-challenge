<?php

namespace BoxedCode\Laravel\Auth\Challenge\Models;

use BoxedCode\Laravel\Auth\Challenge\Contracts\Enrolment as EnrolmentContract;
use BoxedCode\Laravel\Auth\Challenge\Methods\MethodNameFormatter;

class Enrolment extends \Illuminate\Database\Eloquent\Model implements EnrolmentContract
{
    use Concerns\Helpers;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'challenge_enrolments';

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
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param string                                     $method
     *
     * @return void
     */
    public function scopeMethod($query, $method)
    {
        $query->where('method', '=', $method);
    }

    /**
     * Enrolment enrolled status scope.
     *
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param sting                                      $method
     *
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
     * @param \Illuminate\Database\Query\EloquentBuilder $query
     * @param sting                                      $method
     *
     * @return void
     */
    public function scopeEnrolling($query, $method = null)
    {
        $query->whereNull('enrolled_at');

        if ($method) {
            $query->whereMethod($method);
        }
    }
}
