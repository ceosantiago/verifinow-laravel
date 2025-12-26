<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

/**
 * Document Validation Exception
 *
 * Thrown when document validation fails
 */
class DocumentValidationException extends VerifyNowException
{
    public function __construct(string $message = 'Document validation failed')
    {
        parent::__construct($message, 422);
    }
}
