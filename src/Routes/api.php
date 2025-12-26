<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use VerifyNow\Laravel\Http\Controllers\WebhookController;

Route::prefix('api')->middleware(['api'])->group(function () {
    // Webhook endpoint for VerifyNow callbacks
    Route::post('webhooks/verifinow', [WebhookController::class, 'handle'])
        ->middleware('verifinow.webhook.signature')
        ->name('verifinow.webhook');
});
