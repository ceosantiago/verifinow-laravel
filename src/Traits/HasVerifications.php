<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Traits;

use VerifyNow\Laravel\Models\UserAuthentication;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * HasVerifications Trait
 *
 * Add authentication tracking capabilities to any model
 *
 * Usage:
 * class User extends Model {
 *     use HasVerifications;
 * }
 */
trait HasVerifications
{
    /**
     * Get all authentications for this model
     *
     * @return HasMany
     */
    public function authentications(): HasMany
    {
        return $this->hasMany(UserAuthentication::class, 'user_id', 'id');
    }

    /**
     * Check if model has completed authentication
     *
     * @return bool
     */
    public function hasCompletedAuthentication(): bool
    {
        return $this->authentications()
            ->where('status', 'completed')
            ->where('result', 'approved')
            ->exists();
    }

    /**
     * Get latest authentication
     *
     * @return UserAuthentication|null
     */
    public function latestAuthentication(): ?UserAuthentication
    {
        return $this->authentications()
            ->latest('created_at')
            ->first();
    }

    /**
     * Get latest successful authentication
     *
     * @return UserAuthentication|null
     */
    public function lastSuccessfulAuthentication(): ?UserAuthentication
    {
        return $this->authentications()
            ->where('result', 'approved')
            ->latest('authenticated_at')
            ->first();
    }

    /**
     * Check if liveness was detected in last authentication
     *
     * @return bool
     */
    public function lastAuthenticationHasLiveness(): bool
    {
        $auth = $this->latestAuthentication();

        return $auth?->livenessDetected() ?? false;
    }

    /**
     * Check if face matched in last authentication
     *
     * @return bool
     */
    public function lastAuthenticationFaceMatched(): bool
    {
        $auth = $this->latestAuthentication();

        return $auth?->faceMatches() ?? false;
    }

    /**
     * Get average confidence score
     *
     * @return float
     */
    public function averageConfidenceScore(): float
    {
        $average = $this->authentications()
            ->where('status', 'completed')
            ->avg('confidence_score');

        return (float) ($average ?? 0);
    }

    /**
     * Check if requires re-authentication
     *
     * @param int $hoursExpiry Hours after which authentication expires
     * @return bool
     */
    public function requiresReAuthentication(int $hoursExpiry = 24): bool
    {
        $lastAuth = $this->lastSuccessfulAuthentication();

        if (!$lastAuth) {
            return true;
        }

        return $lastAuth->authenticated_at?->addHours($hoursExpiry)->isPast() ?? true;
    }

    /**
     * Get authentication attempt count
     *
     * @return int
     */
    public function authenticationAttemptCount(): int
    {
        return $this->authentications()->count();
    }

    /**
     * Get successful authentication count
     *
     * @return int
     */
    public function successfulAuthenticationCount(): int
    {
        return $this->authentications()
            ->where('result', 'approved')
            ->count();
    }
}
