<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Traits;

use VerifyNow\Laravel\Models\Verification;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Verifiable Trait
 *
 * Add verification capabilities to any model (typically User)
 *
 * Usage:
 * class User extends Model {
 *     use Verifiable;
 * }
 */
trait Verifiable
{
    /**
     * Get all verifications for this model
     *
     * @return HasMany
     */
    public function verifications(): HasMany
    {
        return $this->hasMany(Verification::class, 'user_id', 'id');
    }

    /**
     * Check if model is verified
     *
     * @return bool
     */
    public function isVerified(): bool
    {
        return $this->verifications()
            ->where('status', 'completed')
            ->where('result', 'approved')
            ->exists();
    }

    /**
     * Check if model has pending verification
     *
     * @return bool
     */
    public function hasPendingVerification(): bool
    {
        return $this->verifications()
            ->where('status', '!=', 'completed')
            ->exists();
    }

    /**
     * Get the latest verification
     *
     * @return Verification|null
     */
    public function latestVerification(): ?Verification
    {
        return $this->verifications()
            ->latest('created_at')
            ->first();
    }

    /**
     * Mark model as verified
     *
     * @return bool
     */
    public function markAsVerified(): bool
    {
        return $this->verifications()
            ->where('status', 'completed')
            ->where('result', 'approved')
            ->update(['completed_at' => now()]) > 0;
    }

    /**
     * Get latest approved verification
     *
     * @return Verification|null
     */
    public function lastApprovedVerification(): ?Verification
    {
        return $this->verifications()
            ->where('result', 'approved')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Get latest rejected verification
     *
     * @return Verification|null
     */
    public function lastRejectedVerification(): ?Verification
    {
        return $this->verifications()
            ->where('result', 'rejected')
            ->latest('completed_at')
            ->first();
    }

    /**
     * Check if model requires re-verification
     *
     * @param int $daysExpiry Days after which verification expires
     * @return bool
     */
    public function requiresReverification(int $daysExpiry = 365): bool
    {
        $lastVerification = $this->lastApprovedVerification();

        if (!$lastVerification) {
            return true;
        }

        return $lastVerification->completed_at?->addDays($daysExpiry)->isPast() ?? true;
    }

    /**
     * Get verification count for this model
     *
     * @return int
     */
    public function verificationCount(): int
    {
        return $this->verifications()->count();
    }

    /**
     * Get approval percentage
     *
     * @return float
     */
    public function verificationApprovalRate(): float
    {
        $total = $this->verifications()->count();

        if ($total === 0) {
            return 0;
        }

        $approved = $this->verifications()
            ->where('result', 'approved')
            ->count();

        return ($approved / $total) * 100;
    }
}
