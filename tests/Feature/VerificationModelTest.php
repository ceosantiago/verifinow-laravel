<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests\Feature;

use VerifyNow\Laravel\Tests\TestCase;
use VerifyNow\Laravel\Models\Verification;

/**
 * Verification Model Feature Test
 *
 * Tests Verification model functionality
 */
class VerificationModelTest extends TestCase
{
    /**
     * Test verification model creation
     *
     * @return void
     */
    public function test_verification_can_be_created(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_001',
            'type' => 'idv',
            'country' => 'US',
            'status' => 'pending',
            'result' => 'pending',
        ]);

        $this->assertDatabaseHas('verifications', [
            'verification_id' => 'ver_test_001',
            'user_id' => 1,
        ]);
    }

    /**
     * Test verification completion status
     *
     * @return void
     */
    public function test_verification_can_check_completion_status(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_002',
            'type' => 'idv',
            'status' => 'pending',
            'result' => 'pending',
        ]);

        $this->assertFalse($verification->isCompleted());

        $verification->update(['status' => 'completed']);

        $this->assertTrue($verification->isCompleted());
    }

    /**
     * Test verification approval status
     *
     * @return void
     */
    public function test_verification_can_check_approval_status(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_003',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'pending',
        ]);

        $this->assertFalse($verification->isApproved());

        $verification->update(['result' => 'approved']);

        $this->assertTrue($verification->isApproved());
    }

    /**
     * Test verification rejection status
     *
     * @return void
     */
    public function test_verification_can_check_rejection_status(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_004',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'pending',
        ]);

        $this->assertFalse($verification->isRejected());

        $verification->update(['result' => 'rejected']);

        $this->assertTrue($verification->isRejected());
    }

    /**
     * Test verification pending status
     *
     * @return void
     */
    public function test_verification_can_check_pending_status(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_005',
            'type' => 'idv',
            'status' => 'pending',
            'result' => 'pending',
        ]);

        $this->assertTrue($verification->isPending());

        $verification->update(['status' => 'completed']);

        $this->assertFalse($verification->isPending());
    }

    /**
     * Test verification has many attempts
     *
     * @return void
     */
    public function test_verification_has_many_attempts(): void
    {
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_006',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $verification->attempts()->create([
            'attempt_number' => 1,
            'status' => 'completed',
            'response_code' => 200,
        ]);

        $verification->attempts()->create([
            'attempt_number' => 2,
            'status' => 'completed',
            'response_code' => 200,
        ]);

        $this->assertCount(2, $verification->attempts);
    }
}
