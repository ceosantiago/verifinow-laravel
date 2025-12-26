# VerifyNow Laravel Package - Architecture Guide

## Overview

The VerifyNow Laravel Package is designed following Laravel conventions and best practices. This guide explains the package structure, design patterns, and how components interact.

## Directory Structure

```
src/
├── VerifyNowServiceProvider.php    # Package service provider
├── helpers.php                      # Global helper functions
├── Config/
│   └── verifinow.php               # Package configuration
├── Services/
│   ├── VerifyNowService.php        # Core HTTP client
│   ├── IDVService.php              # Identity verification logic
│   └── AuthenticationService.php   # Facial recognition logic
├── Facades/
│   ├── VerifyNow.php               # Main facade
│   └── VerifyNowManager.php        # Manager facade
├── Http/
│   ├── Controllers/
│   │   └── WebhookController.php   # Webhook handler
│   ├── Middleware/
│   │   ├── VerifyWebhookSignature.php  # Signature validation
│   │   └── RequireVerification.php     # Route protection
│   └── Requests/
│       ├── RequestIDVRequest.php   # IDV validation
│       ├── RequestAuthenticationRequest.php
│       └── CheckVerificationRequest.php
├── Models/
│   ├── Verification.php            # Verification records
│   ├── VerificationAttempt.php     # Attempt tracking
│   └── UserAuthentication.php      # Authentication results
├── Events/
│   ├── VerificationCompleted.php   # Event classes
│   ├── VerificationFailed.php
│   └── AuthenticationCompleted.php
├── Listeners/
│   ├── SendVerificationCompletedNotification.php
│   └── LogVerificationFailed.php
├── Exceptions/
│   ├── VerifyNowException.php      # Base exception
│   ├── UnauthorizedException.php
│   ├── InvalidRequestException.php
│   ├── DocumentValidationException.php
│   ├── LivenessFailedException.php
│   └── FaceMismatchException.php
├── Traits/
│   ├── Verifiable.php              # User model integration
│   └── HasVerifications.php        # Authentication tracking
├── Database/
│   ├── Migrations/
│   │   ├── *_create_verifications_table.php
│   │   ├── *_create_verification_attempts_table.php
│   │   └── *_create_user_authentications_table.php
│   └── Factories/
└── Routes/
    └── api.php                      # Webhook routes

tests/
├── TestCase.php                     # Base test case
├── Unit/
│   ├── ServicesTest.php
│   ├── VerifiableTraitTest.php
│   └── VerificationAttemptTest.php
└── Feature/
    ├── WebhookTest.php
    └── VerificationModelTest.php
```

## Design Patterns

### 1. Service Layer Pattern
**Location**: `src/Services/`

Services encapsulate business logic and API interactions:
- **VerifyNowService**: Low-level HTTP client for VerifyNow API
- **IDVService**: High-level IDV workflow management
- **AuthenticationService**: High-level facial recognition workflow

```
User Input → Form Request → Service Layer → VerifyNow API → Database
```

### 2. Facade Pattern
**Location**: `src/Facades/`

Provides simple, readable API for complex operations:

```php
// Instead of:
app(VerifyNowService::class)->requestIDV($data);

// Users write:
VerifyNow::requestIDV($data);
```

### 3. Trait Pattern
**Location**: `src/Traits/`

Adds verification capabilities to any Eloquent model:

```php
class User extends Model {
    use Verifiable, HasVerifications;
}

$user->isVerified();
$user->latestVerification();
```

### 4. Event-Driven Architecture
**Location**: `src/Events/`, `src/Listeners/`

Decouples verification logic from application-specific actions:

```
Webhook → WebhookController → Model Update → Event Dispatch → Listeners
    ↓
  Email
  Notification
  Log
  Custom Action
```

### 5. Middleware Pattern
**Location**: `src/Http/Middleware/`

Provides cross-cutting concerns:
- **VerifyWebhookSignature**: HMAC-SHA256 validation
- **RequireVerification**: Route-level authorization

## Data Flow

### IDV Verification Flow

```
1. User initiates verification
   ↓
2. Form Request validates input
   ↓
3. Service sends request to VerifyNow API
   ↓
4. Verification record created in database
   ↓
5. User completes verification (uploads document)
   ↓
6. VerifyNow webhook callback received
   ↓
7. WebhookController validates signature
   ↓
8. WebhookController updates verification record
   ↓
9. VerificationCompleted/Failed event dispatched
   ↓
10. Listeners react to event (email, notification, etc.)
```

### Authentication (Facial Recognition) Flow

```
1. User requests authentication
   ↓
2. RequestAuthenticationRequest validates input
   ↓
3. AuthenticationService sends facial recognition request
   ↓
4. UserAuthentication record created
   ↓
5. User completes facial authentication
   ↓
6. VerifyNow sends webhook callback
   ↓
7. WebhookController validates & processes
   ↓
8. AuthenticationCompleted event dispatched
   ↓
9. Listeners handle completion
```

## Component Interactions

### Service Provider Chain

The `VerifyNowServiceProvider` orchestrates package initialization:

```php
register()
├── Merge configuration
├── Register VerifyNowService (singleton)
├── Register IDVService (singleton)
├── Register AuthenticationService (singleton)
├── Register facades
└── Register service bindings

boot()
├── Publish configuration
├── Publish migrations
├── Load migrations
└── Register routes (if enabled)
```

### Model Relationships

```
User
├── hasMany(Verification)
└── hasMany(UserAuthentication)

Verification
├── belongsTo(User)
├── hasMany(VerificationAttempt)
└── hasMany(UserAuthentication)

VerificationAttempt
└── belongsTo(Verification)

UserAuthentication
└── belongsTo(Verification)
```

## Configuration System

Configuration flows from environment → config file → service classes:

```
Environment Variables
        ↓
config/verifinow.php
        ↓
VerifyNowServiceProvider::register()
        ↓
Service Class Constructors
        ↓
Service Methods
```

## HTTP Client Strategy

The package uses Guzzle HTTP for API communication:

1. **Request Building**: Service classes build request arrays
2. **HTTP Call**: VerifyNowService sends actual HTTP request
3. **Response Parsing**: JSON response decoded to array
4. **Error Handling**: Exceptions thrown for API errors
5. **Logging**: All requests/responses logged

## Security Considerations

### 1. API Key Management
- Stored in environment variables only
- Never hardcoded in codebase
- Passed to services via constructor injection

### 2. Webhook Signature Validation
- HMAC-SHA256 signature verification
- Timestamp validation (5-minute window)
- Prevents webhook spoofing

```php
expectedSignature = hash_hmac('sha256', "{$timestamp}.{$payload}", $secret)
// Compare with provided signature using hash_equals (timing-safe)
```

### 3. Database Security
- Foreign key constraints with cascading deletes
- Proper indexing for performance
- No sensitive data in metadata (encrypted if needed)

### 4. Input Validation
- Form Request classes validate all user input
- Enum types for restricted fields
- Country code validation (2-letter ISO codes)

## Extensibility Points

### 1. Custom Services
Extend base services for custom workflows:

```php
class CustomIDVService extends IDVService {
    public function requestWithMetadata(array $data, array $metadata) {
        $data['custom_metadata'] = $metadata;
        return $this->request($data);
    }
}
```

### 2. Custom Listeners
Register listeners for package events:

```php
Event::listen(VerificationCompleted::class, [CustomListener::class, 'handle']);
```

### 3. Custom Middleware
Extend or wrap package middleware:

```php
class CustomVerificationMiddleware extends RequireVerification {
    public function handle($request, Closure $next, $verifyType = 'any') {
        // Custom logic
        return parent::handle($request, $next, $verifyType);
    }
}
```

### 4. Model Observers
Use Laravel observers for automatic event handling:

```php
Verification::observe(VerificationObserver::class);
```

## Testing Architecture

### Test Organization
- **Unit Tests**: Service logic, model methods, helpers
- **Feature Tests**: Webhook processing, model relationships

### Test Fixtures
The TestCase provides mock data helpers:

```php
$this->getMockVerificationResponse()
$this->getCompletedVerificationResponse()
$this->getFailedVerificationResponse()
```

### Testing Best Practices
1. Use fake HTTP client for external API calls
2. Use event faking to verify events are dispatched
3. Test database state changes
4. Validate error conditions
5. Mock sensitive operations

## Code Style

The package follows:
- **PSR-12**: PHP Coding Standards
- **Laravel Conventions**: File naming, namespace structure
- **Strict Types**: `declare(strict_types=1)` in all files
- **Type Hints**: Full parameter and return type declarations
- **Documentation**: PHPDoc comments on all public methods

## Release Process

### Version Strategy
- Semantic Versioning (MAJOR.MINOR.PATCH)
- v1.0.0 - Initial release
- v1.x.x - Backward-compatible features
- v2.0.0 - Breaking changes

### Release Steps
1. Update version in composer.json
2. Update CHANGELOG.md
3. Run full test suite
4. Commit and tag: `git tag -a v1.x.x -m "Release v1.x.x"`
5. Push to GitHub: `git push && git push origin v1.x.x`
6. Packagist automatically syncs

## Common Development Tasks

### Adding a New Exception Type
1. Create file: `src/Exceptions/NewException.php`
2. Extend `VerifyNowException`
3. Define proper HTTP status code
4. Update documentation

### Adding a New Service Method
1. Add method to appropriate service class
2. Add type hints and return types
3. Add logging calls
4. Add unit test
5. Document in README

### Adding a New Model Relationship
1. Add relationship method to model
2. Create foreign key constraint in migration
3. Add methods for checking relationship state
4. Test relationship with unit test

### Adding Webhook Support
1. Add webhook type to WebhookController
2. Create corresponding event class
3. Add listener example
4. Document webhook in README
5. Add webhook test case

## Debugging Tips

### Enable Debug Mode
```env
APP_DEBUG=true
VERIFINOW_LOG_CHANNEL=stack
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Test Webhook Locally
Use ngrok to expose local server:
```bash
ngrok http 8000
```

### Verify Configuration
```php
dd(config('verifinow'));
```

### Test Services Directly
```php
php artisan tinker
>>> $service = app(VerifyNowService::class)
>>> $service->requestIDV(['user_id' => 1, 'country' => 'US'])
```

## Performance Considerations

### Database Queries
- Use eager loading for relationships
- Add indexes on frequently queried columns (user_id, status, etc.)
- Paginate verification listings

### Caching
- Cache verification status if configured
- Use query caching for repeated checks
- Invalidate cache on webhook updates

### Queue Jobs
- Queue webhook processing if under heavy load
- Queue email notifications
- Process batch verifications asynchronously

### API Rate Limiting
- Implement rate limiting for verification requests
- Use exponential backoff for retries
- Monitor API usage

## Troubleshooting Common Issues

### Webhook Not Received
1. Check webhook URL is publicly accessible
2. Verify API key and webhook secret
3. Check firewall/security group rules
4. Review VerifyNow webhook logs

### Signature Validation Failing
1. Ensure webhook secret matches
2. Check system time synchronization
3. Verify request wasn't tampered with
4. Review logs for detailed error

### Models Not Being Created
1. Check migrations have been run
2. Verify database connection
3. Check for foreign key constraint errors
4. Review Laravel logs

### Services Not Resolving
1. Verify service provider is loaded
2. Check composer autoload
3. Clear application cache: `php artisan cache:clear`
4. Rerun composer autoload: `composer dump-autoload`

## Next Steps for Contributors

1. **Read this document** - Understand architecture
2. **Review code** - Study existing implementations
3. **Run tests** - Ensure everything passes locally
4. **Write tests** - Add tests for new features
5. **Follow conventions** - Match existing code style
6. **Document changes** - Update README and comments
7. **Submit PR** - With clear description and tests

