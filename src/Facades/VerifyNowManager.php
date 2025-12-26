<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\AuthenticationService;

/**
 * VerifyNow Manager Facade
 *
 * Provides advanced access to VerifyNow services
 *
 * @method static IDVService idv()
 * @method static AuthenticationService authentication()
 */
class VerifyNowManager extends Facade
{
    /**
     * Get the registered name of the component
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'verifinow-manager';
    }
}
