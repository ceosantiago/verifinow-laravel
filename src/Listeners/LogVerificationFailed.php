<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use VerifyNow\Laravel\Events\VerificationFailed;
use Illuminate\Support\Facades\Log;

/**
 * Log Verification Failed Listener
 *
 * Example listener that logs when verification fails
 *
 * Usage in your app's EventServiceProvider:
 * \VerifyNow\Laravel\Events\VerificationFailed::class => [
 *     \VerifyNow\Laravel\Listeners\LogVerificationFailed::class,
 * ]
 */
class LogVerificationFailed implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Handle the event
     *
     * @param VerificationFailed $event
     * @return void
     */
    public function handle(VerificationFailed $event): void
    {
        $verification = $event->verification;

        Log::channel(config('verifinow.log_channel'))->warning(
            'Verification failed listener triggered',
            [
                'verification_id' => $verification->verification_id,
                'reason' => $verification->metadata['failure_reason'] ?? 'unknown',
            ]
        );

        // Example: Notify admin
        // Log::channel('slack')->error('Verification Failed', ['verification' => $verification]);

        // Example: Update user status
        // $user = $verification->user; // If relationship exists
        // $user->markVerificationFailed();
    }
}
