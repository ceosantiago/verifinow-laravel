# VerifyNow Laravel Package - Development Guide

Complete guide for developing and maintaining the VerifyNow Laravel Package.

## Table of Contents

1. [Development Setup](#development-setup)
2. [Project Structure](#project-structure)
3. [Development Workflow](#development-workflow)
4. [Extending the Package](#extending-the-package)
5. [Testing](#testing)
6. [Debugging](#debugging)
7. [Performance Optimization](#performance-optimization)
8. [Deployment](#deployment)

## Development Setup

### Initial Setup

```bash
# Clone repository
git clone https://github.com/verifinow/laravel.git
cd laravel

# Install dependencies
composer install

# Setup environment
cp .env.example .env.local

# Generate app key (if needed)
php artisan key:generate
```

### IDE Configuration

#### VS Code
Create `.vscode/settings.json`:
```json
{
    "editor.formatOnSave": true,
    "[php]": {
        "editor.defaultFormatter": "DEVSENSE.phptools-vscode",
        "editor.formatOnSave": true
    },
    "editor.codeActionsOnSave": {
        "source.fixAll": true
    }
}
```

#### PhpStorm
- Configure PHP version: 8.3+
- Enable PSR-12 code style
- Configure Laravel plugin
- Setup database inspection

### Development Dependencies

```bash
# Code style
composer require --dev laravel/pint

# Static analysis
composer require --dev phpstan/phpstan

# Testing
composer require --dev pestphp/pest

# Testing Framework
composer require --dev orchestra/testbench
```

## Project Structure

### Source Code (`src/`)

```
src/
├── VerifyNowServiceProvider.php      # Application entry point
├── helpers.php                        # Utility functions
├── Config/                            # Configuration
├── Services/                          # Business logic
├── Models/                            # Eloquent models
├── Http/                              # HTTP layer
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Facades/                           # Facade classes
├── Events/                            # Event classes
├── Listeners/                         # Event listeners
├── Exceptions/                        # Exception classes
├── Traits/                            # Reusable traits
├── Routes/                            # API routes
└── Database/                          # Migrations & factories
    ├── Migrations/
    └── Factories/
```

### Test Structure (`tests/`)

```
tests/
├── TestCase.php                       # Base test class
├── Unit/                              # Unit tests
│   ├── ServicesTest.php
│   ├── VerifiableTraitTest.php
│   └── VerificationAttemptTest.php
└── Feature/                           # Feature tests
    ├── WebhookTest.php
    └── VerificationModelTest.php
```

## Development Workflow

### Adding a New Feature

#### 1. Create Issue
- Document feature requirements
- Discuss implementation approach
- Get feedback from maintainers

#### 2. Create Feature Branch
```bash
git checkout -b feature/your-feature-name
```

#### 3. Write Tests First (TDD)
```php
// tests/Feature/YourFeatureTest.php
class YourFeatureTest extends TestCase {
    public function test_feature_works(): void {
        // Test implementation
    }
}
```

#### 4. Implement Feature
- Follow coding standards
- Add type hints
- Add PHPDoc comments
- Add logging where appropriate

#### 5. Verify Tests Pass
```bash
./vendor/bin/pest
./vendor/bin/pint
./vendor/bin/phpstan analyse src
```

#### 6. Commit and Push
```bash
git add .
git commit -m "[feat] Add your feature description"
git push origin feature/your-feature-name
```

#### 7. Create Pull Request
- Reference issue number
- Document changes
- Request review

### Fixing a Bug

#### 1. Create Bug Report Issue
- Describe bug clearly
- Include reproduction steps
- Include error logs

#### 2. Create Fix Branch
```bash
git checkout -b fix/bug-description
```

#### 3. Write Failing Test
```php
// Demonstrate the bug
public function test_bug_is_reproduced(): void {
    // Test that fails before fix
}
```

#### 4. Implement Fix
- Keep changes minimal
- Focus on the specific bug
- Don't refactor unrelated code

#### 5. Verify Fix Works
```bash
./vendor/bin/pest
# The previously failing test should now pass
```

#### 6. Commit and Create PR
```bash
git commit -m "[fix] Fix description - Fixes #issue_number"
git push origin fix/bug-description
```

### Code Review Process

#### Before Requesting Review
- [ ] All tests pass
- [ ] Code style is clean
- [ ] Static analysis passes
- [ ] Documentation is updated
- [ ] No debug code left

#### During Review
- Respond to feedback
- Make requested changes
- Request re-review if needed
- Be open to suggestions

#### Common Review Comments

| Comment | Action |
|---------|--------|
| "Please add type hints" | Add parameter/return types |
| "Missing test" | Add test for the code |
| "Refactor this" | Improve code clarity |
| "Document this" | Add PHPDoc comment |

## Extending the Package

### Creating Custom Services

```php
// app/Services/CustomVerifyNowService.php
namespace App\Services;

use VerifyNow\Laravel\Services\IDVService;

class CustomIDVService extends IDVService {
    
    /**
     * Request IDV with custom metadata
     */
    public function requestWithMetadata(array $data, array $metadata): array {
        $data['custom_metadata'] = $metadata;
        return parent::request($data);
    }
}

// In service provider:
$this->app->bind(IDVService::class, CustomIDVService::class);
```

### Creating Custom Events

```php
// app/Events/CustomVerificationEvent.php
namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomVerificationEvent {
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public function __construct(public $verification) {}
}
```

### Creating Custom Listeners

```php
// app/Listeners/SendCustomNotification.php
namespace App\Listeners;

use VerifyNow\Laravel\Events\VerificationCompleted;

class SendCustomNotification {
    
    public function handle(VerificationCompleted $event): void {
        $verification = $event->verification;
        
        if ($verification->isApproved()) {
            // Custom notification logic
        }
    }
}
```

### Creating Custom Middleware

```php
// app/Http/Middleware/CustomVerificationMiddleware.php
namespace App\Http\Middleware;

use VerifyNow\Laravel\Http\Middleware\RequireVerification;

class CustomVerificationMiddleware extends RequireVerification {
    
    public function handle($request, Closure $next, $verifyType = 'any') {
        // Custom logic before
        
        $response = parent::handle($request, $next, $verifyType);
        
        // Custom logic after
        
        return $response;
    }
}
```

## Testing

### Running Tests

```bash
# All tests
./vendor/bin/pest

# Specific test file
./vendor/bin/pest tests/Feature/WebhookTest.php

# Specific test method
./vendor/bin/pest tests/Feature/WebhookTest.php --filter test_webhook_signature

# With coverage
./vendor/bin/pest --coverage

# Watch mode
./vendor/bin/pest --watch
```

### Writing Tests

#### Unit Test Example
```php
namespace VerifyNow\Laravel\Tests\Unit;

class MyServiceTest extends TestCase {
    
    public function test_service_can_process(): void {
        $service = app(MyService::class);
        $result = $service->process('data');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('status', $result);
    }
}
```

#### Feature Test Example
```php
namespace VerifyNow\Laravel\Tests\Feature;

class WebhookTest extends TestCase {
    
    public function test_webhook_is_processed(): void {
        $payload = ['type' => 'verification.completed'];
        
        $response = $this->postJson('/api/webhooks/verifinow', $payload, [
            'X-Webhook-Signature' => $this->generateSignature($payload),
            'X-Webhook-Timestamp' => now()->timestamp,
        ]);
        
        $response->assertStatus(200);
    }
}
```

### Test Helpers

Available in TestCase:
```php
$this->getMockVerificationResponse()
$this->getCompletedVerificationResponse()
$this->getFailedVerificationResponse()
$this->getMockAuthenticationResponse()
$this->getCompletedAuthenticationResponse()
```

## Debugging

### Enable Debug Mode

```bash
# In .env.local
APP_DEBUG=true
VERIFINOW_LOG_CHANNEL=stack
```

### Check Logs

```bash
# View recent logs
tail -f storage/logs/laravel.log

# Search for errors
grep -i error storage/logs/laravel.log
```

### Use Tinker

```bash
php artisan tinker

# Test service
>>> $service = app(VerifyNowService::class)
>>> $service->requestIDV(['user_id' => 1, 'country' => 'US'])

# Check configuration
>>> config('verifinow')

# Query database
>>> Verification::where('user_id', 1)->get()
```

### Use Laravel Ray

```bash
composer require --dev spatie/laravel-ray

// In code
ray($variable)->color('red')->label('Debug point');

// Check performance
ray()->measure('my-operation', fn() => expensiveOperation());
```

### XDebug Integration

#### VS Code
```json
{
    "launch": {
        "version": "0.2.0",
        "configurations": [
            {
                "name": "Listen for Xdebug",
                "type": "php",
                "port": 9003
            },
            {
                "name": "Run test",
                "type": "php",
                "request": "launch",
                "program": "${workspaceFolder}/vendor/bin/pest",
                "cwd": "${workspaceFolder}"
            }
        ]
    }
}
```

## Performance Optimization

### Database Optimization

1. **Eager Loading**
```php
// Bad - N+1 query problem
$verifications = Verification::all();
foreach ($verifications as $v) {
    $v->user->name; // Additional query per record
}

// Good - Eager load
$verifications = Verification::with('user')->get();
foreach ($verifications as $v) {
    $v->user->name; // No additional query
}
```

2. **Query Optimization**
```php
// Use select to reduce data transfer
$verifications = Verification::select('id', 'user_id', 'status')
    ->where('status', 'completed')
    ->get();

// Use chunking for large datasets
Verification::chunk(100, function ($verifications) {
    foreach ($verifications as $v) {
        // Process
    }
});
```

3. **Indexing**
Migrations include indexes on:
- `user_id` (frequently filtered)
- `verification_id` (unique lookups)
- `status` (filtering)

### Caching

```php
// Cache verification status
$status = cache()->remember(
    "verification.{$id}",
    config('verifinow.cache_ttl'),
    fn() => $this->checkStatus($id)
);

// Invalidate cache on update
cache()->forget("verification.{$id}");
```

### Async Processing

```php
// Queue webhook processing
dispatch(new ProcessVerificationWebhook($payload))
    ->onQueue('verifinow');

// Queue notifications
dispatch(new SendVerificationNotification($verification))
    ->delay(now()->addMinute());
```

## Deployment

### Pre-Deployment Checks

```bash
# Run full test suite
./vendor/bin/pest

# Check code style
./vendor/bin/pint --check

# Static analysis
./vendor/bin/phpstan analyse src

# Security audit
composer audit
```

### Publishing Configurations

```bash
php artisan vendor:publish \
    --provider="VerifyNow\Laravel\VerifyNowServiceProvider" \
    --tag=verifinow-config
```

### Running Migrations

```bash
# Publish migrations
php artisan vendor:publish \
    --provider="VerifyNow\Laravel\VerifyNowServiceProvider" \
    --tag=verifinow-migrations

# Run migrations
php artisan migrate
```

### Environment Configuration

Required environment variables:
```env
VERIFINOW_API_KEY=sk_live_xxxxx
VERIFINOW_WEBHOOK_SECRET=whsec_xxxxx
VERIFINOW_BASE_URL=https://7on7-backend.verifinow.io
```

Optional environment variables:
```env
VERIFINOW_TIMEOUT=30
VERIFINOW_REGISTER_ROUTES=true
VERIFINOW_QUEUE_VERIFICATIONS=true
VERIFINOW_LOG_CHANNEL=stack
```

### Health Checks

```php
// Check API connectivity
$service = app(VerifyNowService::class);
try {
    $service->checkVerificationStatus('test_id');
} catch (Exception $e) {
    // API is down
}

// Check database
DB::connection()->getPdo();

// Check configuration
config('verifinow.api_key') ? 'OK' : 'ERROR';
```

### Rollback Procedure

If issues occur after deployment:

```bash
# Rollback migration
php artisan migrate:rollback

# Restore previous version
git checkout previous-release-tag

# Clear caches
php artisan cache:clear
php artisan config:clear

# Verify
php artisan migrate
./vendor/bin/pest
```

## Maintenance

### Regular Tasks

- [ ] Review and merge PRs
- [ ] Respond to issues
- [ ] Monitor performance metrics
- [ ] Check security advisories
- [ ] Update dependencies (monthly)
- [ ] Review logs for errors
- [ ] Backup database

### Dependency Updates

```bash
# Check for updates
composer outdated

# Update packages
composer update

# Update specific package
composer update package/name

# Audit for security issues
composer audit
```

### Changelog Maintenance

Keep CHANGELOG.md updated with:
- New features
- Bug fixes
- Breaking changes
- Deprecations
- Security updates

Format: [Keep a Changelog](https://keepachangelog.com/)

## Getting Help

- Review [ARCHITECTURE.md](ARCHITECTURE.md)
- Check existing [issues](https://github.com/verifinow/laravel/issues)
- Read [test files](tests/) for examples
- Review code comments and PHPDoc

