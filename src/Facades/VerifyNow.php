<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use VerifyNow\Laravel\Services\VerifyNowService;

/**
 * VerifyNow Facade
 *
 * Provides easy access to VerifyNow API functionality
 *
 * @method static array requestIDV(array $data)
 * @method static array requestAuthentication(array $data)
 * @method static array checkVerificationStatus(string $verificationId)
 *
 * @see \VerifyNow\Laravel\Services\VerifyNowService
 */
class VerifyNow extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'verifinow';
    }
}
