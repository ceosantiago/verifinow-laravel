<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use VerifyNow\Laravel\Events\VerificationCompleted;
use Illuminate\Support\Facades\Log;

/**
 * Send Verification Completed Notification Listener
 *
 * Example listener that sends notification when verification is completed
 *
 * Usage in your app's EventServiceProvider:
 * \VerifyNow\Laravel\Events\VerificationCompleted::class => [
 *     \VerifyNow\Laravel\Listeners\SendVerificationCompletedNotification::class,
 * ]
 */
class SendVerificationCompletedNotification implements ShouldQueue
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
     * @param VerificationCompleted $event
     * @return void
     */
    public function handle(VerificationCompleted $event): void
    {
        $verification = $event->verification;

        Log::channel(config('verifinow.log_channel'))->info(
            'Verification completed listener triggered',
            ['verification_id' => $verification->verification_id]
        );

        // Example: Send notification to user
        // if ($verification->isApproved()) {
        //     $user = $verification->user; // If relationship exists
        //     $user->notify(new VerificationApprovedNotification($verification));
        // }

        // Example: Update user status
        // if ($verification->isApproved()) {
        //     $user->markAsVerified();
        // }
    }
}
