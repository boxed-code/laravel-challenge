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

    protected $hidden = ['state'];

    protected $dates = ['challenged_at', 'verified_at'];

    public function scopePending($query, $method = null)
    {
        $query->whereNull('verified_at');

        if (!empty($method)) {
            $query->where('method', '=', $method);
        }
    }

    public function scopeMethod($query, $method)
    {
        $query->where('method', '=', $method);
    }

    public function scopeReadyForGc($query, $user_id, $method, $lifetime)
    {
        $query->where('user_id', '=', $user_id)
            ->where('method', '=', $method)
            ->whereIsNull('verified_at')
            ->where('created_at', '<=', now()->subSeconds($lifetime));
    }
}