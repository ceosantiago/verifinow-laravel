<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * Verification Model
 *
 * Represents a verification request and its result
 */
class Verification extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'verifications';

    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'verification_id',
        'type',
        'country',
        'status',
        'result',
        'confidence_score',
        'document_type',
        'metadata',
        'completed_at',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence_score' => 'float',
        'metadata' => AsCollection::class,
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the attempts for this verification
     *
     * @return HasMany
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(VerificationAttempt::class, 'verification_id', 'id');
    }

    /**
     * Check if verification is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if verification is approved
     *
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->result === 'approved';
    }

    /**
     * Check if verification is rejected
     *
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->result === 'rejected';
    }

    /**
     * Check if verification is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
