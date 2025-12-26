<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

/**
 * Invalid Request Exception
 *
 * Thrown when API request data is invalid
 */
class InvalidRequestException extends VerifyNowException
{
    public function __construct(string $message = 'Invalid request data')
    {
        parent::__construct($message, 422);
    }
}
