<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * VerificationAttempt Model
 *
 * Tracks individual verification attempts and responses
 */
class VerificationAttempt extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'verification_attempts';

    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'verification_id',
        'attempt_number',
        'status',
        'response_code',
        'error_message',
        'response_data',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'response_data' => AsCollection::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the verification for this attempt
     *
     * @return BelongsTo
     */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class, 'verification_id', 'id');
    }

    /**
     * Check if attempt was successful
     *
     * @return bool
     */
    public function wasSuccessful(): bool
    {
        return $this->status === 'completed' && $this->response_code === 200;
    }

    /**
     * Check if attempt failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return !$this->wasSuccessful();
    }
}
