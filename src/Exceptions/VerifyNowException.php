<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Exceptions;

use Exception;

/**
 * Base VerifyNow Exception
 */
class VerifyNowException extends Exception
{
    /**
     * Constructor
     *
     * @param string $message Exception message
     * @param int $code Exception code
     */
    public function __construct(string $message = 'VerifyNow API error', int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
