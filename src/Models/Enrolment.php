<?php

namespace BoxedCode\Laravel\TwoFactor\Models;

use BoxedCode\Laravel\TwoFactor\Contracts\Challengeable;
use BoxedCode\Laravel\TwoFactor\Contracts\Enrolment as EnrolmentContract;
use BoxedCode\Laravel\TwoFactor\Methods\MethodNameFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'token',
        'enrolled_at',
        'setup_at',
        'meta',
    ];

    protected $dates = ['enrolled_at', 'setup_at'];

    protected $casts = ['meta' => 'array'];

    public function getLabelAttribute()
    {
        return MethodNameFormatter::toLabel(
            $this->method
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(
            $this->getModelForGuard(),
            'user_id'
        );
    }

    public function scopeMethod($query, $method)
    {
        $query->where('method', '=', $method);
    }

    public function scopeEnrolled($query, $method = null)
    {
        $query->whereNotNull('enrolled_at');

        if ($method) {
            $query->whereMethod($method);
        }
    }

    public function scopeEnrolling($query, $method = null)
    {
        $query->whereNull('enrolled_at');

        if ($method) {
            $query->whereMethod($method);
        }
    }

    public function scopeReadyForGc($query, $user_id, $method, $lifetime)
    {
        $query->where('user_id', '=', $user_id)
            ->where('method', '=', $method)
            ->whereIsNull('enrolled_at')
            ->where('created_at', '<=', now()->subSeconds($lifetime));
    }
}