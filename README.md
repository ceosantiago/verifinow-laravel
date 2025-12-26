# VerifyNow Laravel Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/verifinow/laravel.svg?style=flat-square)](https://packagist.org/packages/verifinow/laravel)
[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

Official Laravel package for VerifyNow Age Verification and Identity Verification services.

## Features

- 🆔 Identity Verification (IDV) - Document-based verification
- 😊 Facial Recognition - Liveness detection and face matching
- 🔐 Webhook Support - Automatic verification status updates
- 🛡️ Signature Verification - Secure webhook validation
- 📊 Database Models - Track all verifications and attempts
- 🎯 Route Middleware - Protect routes with verification requirements
- 📡 Event System - React to verification completion/failure
- 🧪 Fully Tested - Comprehensive test suite included

## Requirements

- PHP 8.3+
- Laravel 11.0+
- Guzzle HTTP 7.0+

## Installation

```bash
composer require verifinow/laravel
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="VerifyNow\Laravel\VerifyNowServiceProvider" --tag=verifinow-config
```

Add to your `.env`:

```env
VERIFINOW_API_KEY=sk_live_xxxxxxxxxxxxx
VERIFINOW_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxx
VERIFINOW_BASE_URL=https://7on7-backend.verifinow.io
VERIFINOW_TIMEOUT=30
VERIFINOW_REGISTER_ROUTES=true
VERIFINOW_QUEUE_VERIFICATIONS=false
```

## Database Setup

Publish and run migrations:

```bash
php artisan vendor:publish --provider="VerifyNow\Laravel\VerifyNowServiceProvider" --tag=verifinow-migrations
php artisan migrate
```

## Usage

### Basic Usage with Facades

```php
use VerifyNow\Laravel\Facades\VerifyNow;

// Request ID Verification
$response = VerifyNow::requestIDV([
    'user_id' => auth()->id(),
    'country' => 'US',
    'document_type' => 'passport',
]);

// Check verification status
$status = VerifyNow::checkVerificationStatus($response['verification_id']);

// Request facial authentication
$auth = VerifyNow::requestAuthentication([
    'user_id' => auth()->id(),
    'verification_id' => $response['verification_id'],
]);
```

### Using Services

```php
use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\AuthenticationService;

// IDV Service
$idvService = app(IDVService::class);
$result = $idvService->request(['user_id' => 1, 'country' => 'US']);
$isApproved = $idvService->isSuccessful($verification_id);

// Authentication Service
$authService = app(AuthenticationService::class);
$result = $authService->request(['user_id' => 1, 'verification_id' => $vid]);
$confidenceScore = $authService->getConfidenceScore($verification_id);
```

### Add Verification to User Model

```php
use VerifyNow\Laravel\Traits\Verifiable;
use VerifyNow\Laravel\Traits\HasVerifications;

class User extends Model
{
    use Verifiable, HasVerifications;
}

// Check if user is verified
$user->isVerified(); // bool

// Get latest verification
$verification = $user->latestVerification();

// Check if requires re-verification
$user->requiresReverification(365); // bool

// Get approval rate
$user->verificationApprovalRate(); // float (0-100)

// Authentication checks
$user->hasCompletedAuthentication(); // bool
$user->lastAuthenticationHasLiveness(); // bool
$user->lastAuthenticationFaceMatched(); // bool
```

### Protect Routes

```php
// Require verification for routes
Route::middleware('verifinow.verified')->group(function () {
    Route::get('/verified-content', function () {
        // Only verified users access
    });
});

// Require specific verification type
Route::middleware('verifinow.verified:idv')->group(function () {
    // Only IDV verified users
});

Route::middleware('verifinow.verified:authentication')->group(function () {
    // Only authenticated users
});
```

### Listen to Events

```php
use VerifyNow\Laravel\Events\VerificationCompleted;
use VerifyNow\Laravel\Events\VerificationFailed;
use Illuminate\Support\Facades\Event;

Event::listen(VerificationCompleted::class, function ($event) {
    $verification = $event->verification;
    
    if ($verification->isApproved()) {
        // Notify user verification succeeded
        $user = $verification->user;
        $user->notify(new VerificationApprovedNotification());
    }
});

Event::listen(VerificationFailed::class, function ($event) {
    // Handle verification failure
    Log::error('Verification failed', ['verification' => $event->verification]);
});
```

### Handle Webhooks

The package automatically handles VerifyNow webhooks at `/api/webhooks/verifinow`:

```php
// Configure webhook in VerifyNow dashboard:
// Webhook URL: https://yourapp.com/api/webhooks/verifinow
// Events: verification.completed, verification.failed, etc.
```

## Testing

Run the test suite:

```bash
./vendor/bin/pest
```

Run specific test file:

```bash
./vendor/bin/pest tests/Feature/WebhookTest.php
```

Generate coverage report:

```bash
./vendor/bin/pest --coverage
```

## Database Schema

### verifications Table
Stores verification requests and results:
- `id` - Primary key
- `user_id` - Associated user
- `verification_id` - VerifyNow verification ID
- `type` - Verification type (idv, authentication, age_verification)
- `country` - Country code
- `status` - Status (pending, processing, completed, failed)
- `result` - Result (approved, rejected, pending)
- `confidence_score` - Confidence score (0-100)
- `document_type` - Document type used
- `metadata` - Additional data
- `completed_at` - Completion timestamp

### verification_attempts Table
Tracks individual verification attempts:
- `id` - Primary key
- `verification_id` - Foreign key to verifications
- `attempt_number` - Attempt sequence number
- `status` - Attempt status
- `response_code` - API response code
- `error_message` - Error details if failed
- `response_data` - Full API response
- `ip_address` - Request IP
- `user_agent` - Request user agent

### user_authentications Table
Stores facial authentication results:
- `id` - Primary key
- `verification_id` - Foreign key to verifications
- `user_id` - Associated user
- `authentication_id` - VerifyNow authentication ID
- `status` - Status (pending, processing, completed, failed)
- `result` - Result (approved, rejected, pending)
- `confidence_score` - Overall confidence
- `liveness_score` - Liveness detection score
- `face_match_score` - Face matching score
- `device_info` - Device information
- `location_data` - Location data
- `authenticated_at` - Authentication timestamp

## Configuration Options

All configuration options can be set via environment variables:

```env
# API Configuration
VERIFINOW_API_KEY=          # Your VerifyNow API key (required)
VERIFINOW_BASE_URL=         # VerifyNow API base URL
VERIFINOW_WEBHOOK_SECRET=   # Webhook signature secret (required)
VERIFINOW_TIMEOUT=          # Request timeout in seconds (default: 30)

# Feature Flags
VERIFINOW_REGISTER_ROUTES=  # Auto-register webhook routes (default: true)
VERIFINOW_QUEUE_VERIFICATIONS=  # Queue verification jobs (default: false)
VERIFINOW_CACHE_VERIFICATIONS=  # Cache verification results (default: true)
VERIFINOW_CACHE_TTL=        # Cache TTL in seconds (default: 3600)

# Retry Configuration
VERIFINOW_RETRY_FAILED=     # Retry failed verifications (default: true)
VERIFINOW_MAX_RETRIES=      # Maximum retry attempts (default: 3)

# Logging
VERIFINOW_LOG_CHANNEL=      # Log channel (default: single)
```

## Helper Functions

Convenient helper functions are available:

```php
// Get the main service
verify_now();

// Get the manager
verifinow_manager();

// Request IDV verification
request_idv(['user_id' => 1, 'country' => 'US']);

// Request authentication
request_authentication(['user_id' => 1, 'verification_id' => 'ver_xxx']);

// Check verification status
check_verification_status('ver_xxx');
```

## Error Handling

The package throws specific exceptions for different scenarios:

```php
use VerifyNow\Laravel\Exceptions\{
    VerifyNowException,
    UnauthorizedException,
    InvalidRequestException,
    DocumentValidationException,
    LivenessFailedException,
    FaceMismatchException,
};

try {
    VerifyNow::requestIDV($data);
} catch (UnauthorizedException $e) {
    // Invalid API key
} catch (InvalidRequestException $e) {
    // Invalid request data
} catch (DocumentValidationException $e) {
    // Document quality/format issue
} catch (LivenessFailedException $e) {
    // Liveness detection failed
} catch (FaceMismatchException $e) {
    // Face doesn't match document
} catch (VerifyNowException $e) {
    // Generic VerifyNow API error
}
```

## Documentation

See the `docs/` folder for complete documentation:
- [Integration Guide](../docs/VERIFINOW_API_INTEGRATION.md)
- [Setup Guide](../docs/PACKAGE_SETUP_GUIDE.md)
- [Implementation Examples](../docs/IMPLEMENTATION_EXAMPLES.md)

## Support

For issues, questions, or contributions, please visit:
- GitHub: https://github.com/verifinow/laravel
- VerifyNow: https://verifinow.io

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for details on what has changed recently.
