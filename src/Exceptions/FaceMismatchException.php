<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

/**
 * Face Mismatch Exception
 *
 * Thrown when face doesn't match document
 */
class FaceMismatchException extends VerifyNowException
{
    public function __construct(string $message = 'Face does not match document')
    {
        parent::__construct($message, 422);
    }
}
