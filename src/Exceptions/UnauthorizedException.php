<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

/**
 * Unauthorized Exception
 *
 * Thrown when API key is invalid or unauthorized
 */
class UnauthorizedException extends VerifyNowException
{
    public function __construct(string $message = 'Unauthorized: Invalid API key')
    {
        parent::__construct($message, 401);
    }
}
