<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\AsCollection;

/**
 * UserAuthentication Model
 *
 * Stores facial authentication results and confidence scores
 */
class UserAuthentication extends Model
{
    /**
     * The table associated with the model
     *
     * @var string
     */
    protected $table = 'user_authentications';

    /**
     * The attributes that are mass assignable
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'verification_id',
        'user_id',
        'authentication_id',
        'status',
        'result',
        'confidence_score',
        'liveness_score',
        'face_match_score',
        'device_info',
        'location_data',
        'authenticated_at',
    ];

    /**
     * The attributes that should be cast
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence_score' => 'float',
        'liveness_score' => 'float',
        'face_match_score' => 'float',
        'device_info' => AsCollection::class,
        'location_data' => AsCollection::class,
        'authenticated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the verification for this authentication
     *
     * @return BelongsTo
     */
    public function verification(): BelongsTo
    {
        return $this->belongsTo(Verification::class, 'verification_id', 'id');
    }

    /**
     * Check if authentication is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'completed' && $this->result === 'approved';
    }

    /**
     * Check if authentication is completed
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if liveness detection passed
     *
     * @return bool
     */
    public function livenessDetected(): bool
    {
        return $this->liveness_score > 70;
    }

    /**
     * Check if face matches document
     *
     * @return bool
     */
    public function faceMatches(): bool
    {
        return $this->face_match_score > 80;
    }
}
