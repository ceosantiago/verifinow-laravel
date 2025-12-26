<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests\Unit;

use VerifyNow\Laravel\Tests\TestCase;
use VerifyNow\Laravel\Models\Verification;
use VerifyNow\Laravel\Models\VerificationAttempt;

/**
 * VerificationAttempt Model Unit Test
 *
 * Tests VerificationAttempt model functionality
 */
class VerificationAttemptTest extends TestCase
{
    /**
     * Test attempt can be created
     *
     * @return void
     */
    public function test_verification_attempt_can_be_created(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_attempt_001',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $attempt = VerificationAttempt::create([
            'verification_id' => $verification->id,
            'attempt_number' => 1,
            'status' => 'completed',
            'response_code' => 200,
        ]);

        $this->assertDatabaseHas('verification_attempts', [
            'verification_id' => $verification->id,
            'attempt_number' => 1,
        ]);
    }

    /**
     * Test attempt belongs to verification
     *
     * @return void
     */
    public function test_attempt_belongs_to_verification(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_attempt_002',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $attempt = VerificationAttempt::create([
            'verification_id' => $verification->id,
            'attempt_number' => 1,
            'status' => 'completed',
            'response_code' => 200,
        ]);

        $this->assertEquals($verification->id, $attempt->verification->id);
    }

    /**
     * Test can check if attempt was successful
     *
     * @return void
     */
    public function test_can_check_if_attempt_was_successful(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_attempt_003',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $successAttempt = VerificationAttempt::create([
            'verification_id' => $verification->id,
            'attempt_number' => 1,
            'status' => 'completed',
            'response_code' => 200,
        ]);

        $failedAttempt = VerificationAttempt::create([
            'verification_id' => $verification->id,
            'attempt_number' => 2,
            'status' => 'failed',
            'response_code' => 500,
        ]);

        $this->assertTrue($successAttempt->wasSuccessful());
        $this->assertFalse($failedAttempt->wasSuccessful());
    }
}
