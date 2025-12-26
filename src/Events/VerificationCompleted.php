<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VerifyNow\Laravel\Models\Verification;

/**
 * Verification Completed Event
 *
 * Fired when a verification is completed successfully
 */
class VerificationCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Constructor
     *
     * @param Verification $verification
     */
    public function __construct(
        public Verification $verification
    ) {
    }
}
