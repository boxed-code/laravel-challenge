<?php

namespace BoxedCode\Laravel\TwoFactor\Models;

use BoxedCode\Laravel\TwoFactor\Contracts\Challenge as ChallengeContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'token',
        'user_id',
        'method',
        'purpose',
        'challenged_at',
        'verified_at',
        'meta',
    ];

    protected $hidden = ['token'];

    protected $dates = ['challenged_at', 'verified_at'];

    protected $casts = ['meta' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'user_id'
        );
    }

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