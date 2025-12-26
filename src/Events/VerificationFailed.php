<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VerifyNow\Laravel\Models\Verification;

/**
 * Verification Failed Event
 *
 * Fired when a verification fails
 */
class VerificationFailed
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
