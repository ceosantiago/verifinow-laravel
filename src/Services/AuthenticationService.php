<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Services;

use VerifyNow\Laravel\Exceptions\VerifyNowException;

/**
 * Authentication Service (Facial Recognition)
 *
 * Handles facial recognition and liveness detection
 */
class AuthenticationService
{
    /**
     * Constructor
     *
     * @param VerifyNowService $service Core VerifyNow service
     */
    public function __construct(private VerifyNowService $service)
    {
    }

    /**
     * Request Authentication (Facial Recognition)
     *
     * @param array<string, mixed> $data Authentication request data (user_id, verification_id, etc.)
     * @return array<string, mixed> Authentication response
     *
     * @throws VerifyNowException
     */
    public function request(array $data): array
    {
        return $this->service->requestAuthentication($data);
    }

    /**
     * Check Authentication Status
     *
     * @param string $verificationId Verification ID
     * @return array<string, mixed> Status response
     *
     * @throws VerifyNowException
     */
    public function checkStatus(string $verificationId): array
    {
        return $this->service->checkVerificationStatus($verificationId);
    }

    /**
     * Verify if Authentication is completed
     *
     * @param string $verificationId Verification ID
     * @return bool True if authentication is completed
     *
     * @throws VerifyNowException
     */
    public function isCompleted(string $verificationId): bool
    {
        $status = $this->checkStatus($verificationId);

        return isset($status['status']) && $status['status'] === 'completed';
    }

    /**
     * Verify if Authentication is successful
     *
     * @param string $verificationId Verification ID
     * @return bool True if authentication is successful
     *
     * @throws VerifyNowException
     */
    public function isSuccessful(string $verificationId): bool
    {
        $status = $this->checkStatus($verificationId);

        return isset($status['result']) && $status['result'] === 'approved';
    }

    /**
     * Get Confidence Score
     *
     * @param string $verificationId Verification ID
     * @return float Confidence score (0-100)
     *
     * @throws VerifyNowException
     */
    public function getConfidenceScore(string $verificationId): float
    {
        $status = $this->checkStatus($verificationId);

        return $status['confidence_score'] ?? 0.0;
    }
}
