<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use VerifyNow\Laravel\Models\UserAuthentication;

/**
 * Authentication Completed Event
 *
 * Fired when facial authentication is completed successfully
 */
class AuthenticationCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Constructor
     *
     * @param UserAuthentication $authentication
     */
    public function __construct(
        public UserAuthentication $authentication
    ) {
    }
}
