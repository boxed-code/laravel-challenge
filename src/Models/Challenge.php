<?php

namespace BoxedCode\Laravel\TwoFactor\Models;

use BoxedCode\Laravel\TwoFactor\Contracts\Challenge as ChallengeContract;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model implements ChallengeContract
{
    use Concerns\Helpers;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'two_factor_challenges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'user_id',
        'method',
        'purpose',
        'challenged_at',
        'verified_at',
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
    protected $dates = ['challenged_at', 'verified_at'];

    /**
     * Pending challenges scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  string $method
     * @param  string $purpose
     * @return void
     */
    public function scopePending($query, $method = null, $purpose = null)
    {
        $query->whereNull('verified_at');

        if (!empty($method)) {
            $query->where('method', '=', $method);
        }

        if (!empty($purpose)) {
            $query->where('purpose', '=', $purpose);
        }
    }

    /**
     * Challenge method scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  string $method
     * @param  string|null $purpose
     * @return void
     */
    public function scopeMethod($query, $method, $purpose = null)
    {
        $query->where('method', '=', $method);

        if ($purpose) {
            $query->where('purpose', '=', $purpose);
        }
    }

    /**
     * Challenge method scope.
     * 
     * @param  \Illuminate\Database\Query\EloquentBuilder $query
     * @param  sting $method
     * @return void
     */
    public function scopeEnrolment($query, $method)
    {
        $query
            ->where('method', '=', $method)
            ->where('purpose', '=', static::PURPOSE_ENROLMENT);
    }
}