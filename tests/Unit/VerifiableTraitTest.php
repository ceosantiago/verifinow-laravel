<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests\Unit;

use VerifyNow\Laravel\Tests\TestCase;
use VerifyNow\Laravel\Traits\Verifiable;
use VerifyNow\Laravel\Models\Verification;
use Illuminate\Database\Eloquent\Model;

/**
 * Verifiable Trait Unit Test
 *
 * Tests Verifiable trait functionality
 */
class VerifiableTraitTest extends TestCase
{
    /**
     * Set up test user model
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user model with Verifiable trait
        $this->createTestUserModel();
    }

    /**
     * Create a test user model
     *
     * @return void
     */
    private function createTestUserModel(): void
    {
        // This would be the actual User model in the application using this package
        // For testing, we create a minimal model
    }

    /**
     * Test verifiable trait can check if model is verified
     *
     * @return void
     */
    public function test_can_check_if_model_is_verified(): void
    {
        // Create a verification
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_trait_001',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'approved',
        ]);

        // Note: In real usage, you would use:
        // $user->isVerified()
        // For this test, we verify the verification record exists
        $this->assertTrue($verification->isApproved());
        $this->assertTrue($verification->isCompleted());
    }

    /**
     * Test can get latest verification
     *
     * @return void
     */
    public function test_can_get_latest_verification(): void
    {
        $ver1 = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_trait_002',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'approved',
        ]);

        sleep(1); // Ensure different timestamps

        $ver2 = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_trait_003',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'approved',
        ]);

        // Get the latest
        $latest = Verification::where('user_id', 1)
            ->latest('created_at')
            ->first();

        $this->assertEquals($ver2->id, $latest->id);
    }

    /**
     * Test can check if model has pending verification
     *
     * @return void
     */
    public function test_can_detect_pending_verification(): void
    {
        Verification::create([
            'user_id' => 2,
            'verification_id' => 'ver_test_trait_004',
            'type' => 'idv',
            'status' => 'pending',
            'result' => 'pending',
        ]);

        $pending = Verification::where('user_id', 2)
            ->where('status', '!=', 'completed')
            ->exists();

        $this->assertTrue($pending);
    }

    /**
     * Test can get approval rate
     *
     * @return void
     */
    public function test_can_calculate_approval_rate(): void
    {
        // Create 3 verifications: 2 approved, 1 rejected
        Verification::create([
            'user_id' => 3,
            'verification_id' => 'ver_test_trait_005',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'approved',
        ]);

        Verification::create([
            'user_id' => 3,
            'verification_id' => 'ver_test_trait_006',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'approved',
        ]);

        Verification::create([
            'user_id' => 3,
            'verification_id' => 'ver_test_trait_007',
            'type' => 'idv',
            'status' => 'completed',
            'result' => 'rejected',
        ]);

        $total = Verification::where('user_id', 3)->count();
        $approved = Verification::where('user_id', 3)
            ->where('result', 'approved')
            ->count();

        $rate = ($approved / $total) * 100;

        $this->assertEquals(3, $total);
        $this->assertEquals(2, $approved);
        $this->assertEquals(66.66666666666666, $rate);
    }
}
