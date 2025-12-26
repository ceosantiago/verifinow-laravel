<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

/**
 * Liveness Failed Exception
 *
 * Thrown when liveness detection fails
 */
class LivenessFailedException extends VerifyNowException
{
    public function __construct(string $message = 'Liveness detection failed')
    {
        parent::__construct($message, 422);
    }
}
