<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Services;

use VerifyNow\Laravel\Exceptions\VerifyNowException;

/**
 * Identity Verification (IDV) Service
 *
 * Handles identity verification workflows
 */
class IDVService
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
     * Request IDV Verification
     *
     * @param array<string, mixed> $data IDV request data (user_id, country, etc.)
     * @return array<string, mixed> Verification response with verification_id
     *
     * @throws VerifyNowException
     */
    public function request(array $data): array
    {
        return $this->service->requestIDV($data);
    }

    /**
     * Check IDV Status
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
     * Verify if IDV is completed
     *
     * @param string $verificationId Verification ID
     * @return bool True if verification is completed
     *
     * @throws VerifyNowException
     */
    public function isCompleted(string $verificationId): bool
    {
        $status = $this->checkStatus($verificationId);

        return isset($status['status']) && $status['status'] === 'completed';
    }

    /**
     * Verify if IDV is successful
     *
     * @param string $verificationId Verification ID
     * @return bool True if verification is successful
     *
     * @throws VerifyNowException
     */
    public function isSuccessful(string $verificationId): bool
    {
        $status = $this->checkStatus($verificationId);

        return isset($status['result']) && $status['result'] === 'approved';
    }
}
