<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use VerifyNow\Laravel\Exceptions\VerifyNowException;

/**
 * Verify Webhook Signature Middleware
 *
 * Validates that webhook requests are coming from VerifyNow
 * by checking HMAC-SHA256 signature
 */
class VerifyWebhookSignature
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     *
     * @throws VerifyNowException
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $signature = $request->header('X-Webhook-Signature');
        $timestamp = $request->header('X-Webhook-Timestamp');

        // Verify signature is present
        if (!$signature || !$timestamp) {
            Log::channel(config('verifinow.log_channel'))->warning(
                'Webhook request missing signature or timestamp',
                ['ip' => $request->ip()]
            );

            return response()->json([
                'message' => 'Missing webhook signature or timestamp',
            ], 401);
        }

        // Verify timestamp is recent (within 5 minutes)
        $requestTime = (int) $timestamp;
        $currentTime = now()->timestamp;
        $timeWindow = 300; // 5 minutes

        if (abs($currentTime - $requestTime) > $timeWindow) {
            Log::channel(config('verifinow.log_channel'))->warning(
                'Webhook timestamp outside acceptable window',
                ['timestamp' => $timestamp, 'current_time' => $currentTime]
            );

            return response()->json([
                'message' => 'Webhook timestamp outside acceptable window',
            ], 401);
        }

        // Verify HMAC signature
        $secret = config('verifinow.webhook_secret');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::channel(config('verifinow.log_channel'))->error(
                'Webhook signature verification failed',
                [
                    'ip' => $request->ip(),
                    'expected' => $expectedSignature,
                    'received' => $signature,
                ]
            );

            return response()->json([
                'message' => 'Invalid webhook signature',
            ], 401);
        }

        Log::channel(config('verifinow.log_channel'))->info(
            'Webhook signature verified successfully',
            ['ip' => $request->ip()]
        );

        return $next($request);
    }
}
