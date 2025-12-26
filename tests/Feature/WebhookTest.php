<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Tests\Feature;

use VerifyNow\Laravel\Tests\TestCase;
use VerifyNow\Laravel\Models\Verification;
use VerifyNow\Laravel\Events\VerificationCompleted;
use VerifyNow\Laravel\Events\VerificationFailed;
use Illuminate\Support\Facades\Event;

/**
 * Webhook Feature Test
 *
 * Tests webhook endpoint functionality
 */
class WebhookTest extends TestCase
{
    /**
     * Test webhook signature verification success
     *
     * @return void
     */
    public function test_webhook_with_valid_signature_is_accepted(): void
    {
        Event::fake();

        $payload = json_encode([
            'type' => 'verification.completed',
            'verification_id' => 'ver_test_123',
            'result' => 'approved',
            'confidence_score' => 95.5,
        ]);

        $timestamp = now()->timestamp;
        $secret = config('verifinow.webhook_secret');
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        $response = $this->withHeaders([
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
        ])->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Webhook processed successfully']);
    }

    /**
     * Test webhook without signature is rejected
     *
     * @return void
     */
    public function test_webhook_without_signature_is_rejected(): void
    {
        $payload = json_encode([
            'type' => 'verification.completed',
            'verification_id' => 'ver_test_123',
        ]);

        $response = $this->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Missing webhook signature or timestamp']);
    }

    /**
     * Test webhook with invalid signature is rejected
     *
     * @return void
     */
    public function test_webhook_with_invalid_signature_is_rejected(): void
    {
        $payload = json_encode([
            'type' => 'verification.completed',
            'verification_id' => 'ver_test_123',
        ]);

        $timestamp = now()->timestamp;

        $response = $this->withHeaders([
            'X-Webhook-Signature' => 'invalid_signature_here',
            'X-Webhook-Timestamp' => $timestamp,
        ])->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid webhook signature']);
    }

    /**
     * Test webhook with old timestamp is rejected
     *
     * @return void
     */
    public function test_webhook_with_old_timestamp_is_rejected(): void
    {
        $payload = json_encode([
            'type' => 'verification.completed',
            'verification_id' => 'ver_test_123',
        ]);

        $oldTimestamp = now()->subMinutes(10)->timestamp;
        $secret = config('verifinow.webhook_secret');
        $signature = hash_hmac('sha256', "{$oldTimestamp}.{$payload}", $secret);

        $response = $this->withHeaders([
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $oldTimestamp,
        ])->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(401)
            ->assertJson(['message' => 'Webhook timestamp outside acceptable window']);
    }

    /**
     * Test verification completed webhook creates record
     *
     * @return void
     */
    public function test_verification_completed_webhook_creates_record(): void
    {
        Event::fake();

        // Create initial verification
        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_123',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $payload = json_encode([
            'type' => 'verification.completed',
            'verification_id' => 'ver_test_123',
            'result' => 'approved',
            'confidence_score' => 95.5,
        ]);

        $timestamp = now()->timestamp;
        $secret = config('verifinow.webhook_secret');
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        $response = $this->withHeaders([
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
        ])->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(200);

        $this->assertDatabaseHas('verifications', [
            'verification_id' => 'ver_test_123',
            'status' => 'completed',
            'result' => 'approved',
            'confidence_score' => 95.5,
        ]);

        Event::assertDispatched(VerificationCompleted::class);
    }

    /**
     * Test verification failed webhook
     *
     * @return void
     */
    public function test_verification_failed_webhook_marks_as_rejected(): void
    {
        Event::fake();

        $verification = Verification::create([
            'user_id' => 1,
            'verification_id' => 'ver_test_456',
            'type' => 'idv',
            'status' => 'pending',
        ]);

        $payload = json_encode([
            'type' => 'verification.failed',
            'verification_id' => 'ver_test_456',
            'reason' => 'document_quality_low',
        ]);

        $timestamp = now()->timestamp;
        $secret = config('verifinow.webhook_secret');
        $signature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        $response = $this->withHeaders([
            'X-Webhook-Signature' => $signature,
            'X-Webhook-Timestamp' => $timestamp,
        ])->postJson('/api/webhooks/verifinow', json_decode($payload, true));

        $response->assertStatus(200);

        $this->assertDatabaseHas('verifications', [
            'verification_id' => 'ver_test_456',
            'status' => 'failed',
            'result' => 'rejected',
        ]);

        Event::assertDispatched(VerificationFailed::class);
    }
}
