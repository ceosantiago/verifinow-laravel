<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use VerifyNow\Laravel\Models\Verification;
use VerifyNow\Laravel\Models\UserAuthentication;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Event;

/**
 * Webhook Controller
 *
 * Handles incoming webhooks from VerifyNow API
 */
class WebhookController extends Controller
{
    /**
     * Handle webhook from VerifyNow
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        try {
            $data = $request->json()->all();

            Log::channel(config('verifinow.log_channel'))->info(
                'VerifyNow webhook received',
                ['data' => $data]
            );

            // Determine webhook type and handle accordingly
            $type = $data['type'] ?? null;

            match ($type) {
                'verification.completed' => $this->handleVerificationCompleted($data),
                'verification.failed' => $this->handleVerificationFailed($data),
                'authentication.completed' => $this->handleAuthenticationCompleted($data),
                'authentication.failed' => $this->handleAuthenticationFailed($data),
                default => Log::channel(config('verifinow.log_channel'))->warning(
                    'Unknown webhook type received',
                    ['type' => $type]
                ),
            };

            return response()->json([
                'message' => 'Webhook processed successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::channel(config('verifinow.log_channel'))->error(
                'Webhook processing error',
                ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]
            );

            return response()->json([
                'message' => 'Webhook processing failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle verification completed webhook
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleVerificationCompleted(array $data): void
    {
        $verificationId = $data['verification_id'] ?? null;
        $result = $data['result'] ?? 'pending';
        $confidenceScore = $data['confidence_score'] ?? 0;

        if (!$verificationId) {
            return;
        }

        $verification = Verification::where('verification_id', $verificationId)->first();

        if ($verification) {
            $verification->update([
                'status' => 'completed',
                'result' => $result,
                'confidence_score' => $confidenceScore,
                'completed_at' => now(),
            ]);

            Log::channel(config('verifinow.log_channel'))->info(
                'Verification completed',
                ['verification_id' => $verificationId, 'result' => $result]
            );

            // Dispatch event
            Event::dispatch(
                new \VerifyNow\Laravel\Events\VerificationCompleted($verification)
            );
        }
    }

    /**
     * Handle verification failed webhook
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleVerificationFailed(array $data): void
    {
        $verificationId = $data['verification_id'] ?? null;
        $reason = $data['reason'] ?? 'unknown';

        if (!$verificationId) {
            return;
        }

        $verification = Verification::where('verification_id', $verificationId)->first();

        if ($verification) {
            $verification->update([
                'status' => 'failed',
                'result' => 'rejected',
                'metadata' => [
                    'failure_reason' => $reason,
                ],
            ]);

            Log::channel(config('verifinow.log_channel'))->warning(
                'Verification failed',
                ['verification_id' => $verificationId, 'reason' => $reason]
            );

            // Dispatch event
            Event::dispatch(
                new \VerifyNow\Laravel\Events\VerificationFailed($verification)
            );
        }
    }

    /**
     * Handle authentication completed webhook
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleAuthenticationCompleted(array $data): void
    {
        $authenticationId = $data['authentication_id'] ?? null;
        $verificationId = $data['verification_id'] ?? null;
        $result = $data['result'] ?? 'pending';
        $confidenceScore = $data['confidence_score'] ?? 0;
        $livenessScore = $data['liveness_score'] ?? 0;
        $faceMatchScore = $data['face_match_score'] ?? 0;

        if (!$authenticationId) {
            return;
        }

        $authentication = UserAuthentication::updateOrCreate(
            ['authentication_id' => $authenticationId],
            [
                'verification_id' => $verificationId,
                'status' => 'completed',
                'result' => $result,
                'confidence_score' => $confidenceScore,
                'liveness_score' => $livenessScore,
                'face_match_score' => $faceMatchScore,
                'authenticated_at' => now(),
            ]
        );

        Log::channel(config('verifinow.log_channel'))->info(
            'Authentication completed',
            ['authentication_id' => $authenticationId, 'result' => $result]
        );

        // Dispatch event
        Event::dispatch(
            new \VerifyNow\Laravel\Events\AuthenticationCompleted($authentication)
        );
    }

    /**
     * Handle authentication failed webhook
     *
     * @param array<string, mixed> $data
     * @return void
     */
    private function handleAuthenticationFailed(array $data): void
    {
        $authenticationId = $data['authentication_id'] ?? null;
        $reason = $data['reason'] ?? 'unknown';

        if (!$authenticationId) {
            return;
        }

        $authentication = UserAuthentication::updateOrCreate(
            ['authentication_id' => $authenticationId],
            [
                'status' => 'failed',
                'result' => 'rejected',
                'device_info' => [
                    'failure_reason' => $reason,
                ],
            ]
        );

        Log::channel(config('verifinow.log_channel'))->warning(
            'Authentication failed',
            ['authentication_id' => $authenticationId, 'reason' => $reason]
        );

        // Dispatch event - would need an AuthenticationFailed event
    }
}
