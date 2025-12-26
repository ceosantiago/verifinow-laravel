<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Require Verification Middleware
 *
 * Protects routes by ensuring user is verified
 * Usage: Route::middleware('verifinow.verified')->...
 */
class RequireVerification
{
    /**
     * Handle an incoming request
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $verifyType Verification type (idv, authentication, any)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ?string $verifyType = 'any'): mixed
    {
        // Skip check if user not authenticated
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = $request->user();

        // Check if user model has verification capability
        if (!method_exists($user, 'isVerified')) {
            return response()->json([
                'message' => 'User model does not support verification',
            ], 500);
        }

        // Check verification status
        $isVerified = match ($verifyType) {
            'idv' => $user->verifications()
                ->where('type', 'idv')
                ->where('status', 'completed')
                ->where('result', 'approved')
                ->exists(),
            'authentication' => $user->authentications()
                ->where('status', 'completed')
                ->where('result', 'approved')
                ->exists(),
            default => $user->isVerified(),
        };

        if (!$isVerified) {
            return response()->json([
                'message' => 'User is not verified',
                'verification_required' => $verifyType,
            ], 403);
        }

        return $next($request);
    }
}
