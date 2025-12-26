# Contributing to VerifyNow Laravel Package

Thank you for your interest in contributing to the VerifyNow Laravel Package! This guide will help you get started.

## Code of Conduct

This project is committed to providing a welcoming and inspiring community. Please read and follow our code of conduct:

- Be respectful and inclusive
- Welcome people of all backgrounds
- Report unacceptable behavior
- Focus on constructive feedback

## Getting Started

### Prerequisites
- PHP 8.3+
- Laravel 11.0+
- Composer
- Git
- A GitHub account

### Setup Development Environment

1. **Clone the repository**
```bash
git clone https://github.com/verifinow/laravel.git
cd laravel
```

2. **Install dependencies**
```bash
composer install
```

3. **Create environment file**
```bash
cp .env.example .env.local
```

4. **Configure testing**
```bash
# Set test configuration in .env.local
VERIFINOW_API_KEY=test_key
VERIFINOW_WEBHOOK_SECRET=test_secret
```

### Running Tests Locally

```bash
# Run all tests
./vendor/bin/pest

# Run with coverage
./vendor/bin/pest --coverage

# Run specific test file
./vendor/bin/pest tests/Feature/WebhookTest.php

# Run unit tests only
./vendor/bin/pest tests/Unit

# Watch mode
./vendor/bin/pest --watch
```

## How to Contribute

### Reporting Bugs

Before reporting a bug, please search existing issues. If you find a bug:

1. **Create an issue** with a clear title
2. **Describe the bug** with steps to reproduce
3. **Include environment** (PHP version, Laravel version, package version)
4. **Include logs** (Laravel logs, VerifyNow API response)
5. **Include expected vs actual** behavior

**Issue Template:**
```markdown
## Description
[Clear description of the bug]

## Steps to Reproduce
1. Step 1
2. Step 2
3. etc

## Environment
- PHP Version: 8.3.x
- Laravel Version: 11.0
- Package Version: 1.0.0

## Error Message
[Full error message and stack trace]

## Expected Behavior
[What should happen]

## Actual Behavior
[What actually happens]
```

### Suggesting Enhancements

Enhancement suggestions should include:

1. **Clear description** of the feature
2. **Use cases** where it would be helpful
3. **Examples** of how it might be used
4. **Potential drawbacks** or considerations

**Enhancement Template:**
```markdown
## Feature Request
[Clear title of the feature]

## Description
[Detailed description]

## Use Cases
- Use case 1
- Use case 2

## Example Usage
```php
// How it would be used
```

## Potential Drawbacks
[Any concerns or drawbacks]
```

### Submitting Changes

#### 1. Fork the Repository
```bash
# Go to https://github.com/verifinow/laravel
# Click "Fork" button
# Clone your fork:
git clone https://github.com/YOUR_USERNAME/laravel.git
cd laravel
git remote add upstream https://github.com/verifinow/laravel.git
```

#### 2. Create a Feature Branch
```bash
# Update from main
git fetch upstream
git checkout main
git merge upstream/main

# Create feature branch
git checkout -b feature/my-feature

# Or for bugfix
git checkout -b fix/my-bugfix
```

#### 3. Make Your Changes

**Follow these guidelines:**

- **Code Style**: Run `./vendor/bin/pint` to fix formatting
- **Type Hints**: Always add parameter and return types
- **PHPDoc**: Document all public methods
- **Tests**: Add tests for new features
- **One commit per feature**: Keep commits logical and atomic
- **Meaningful messages**: Write clear commit messages

**Commit Message Format:**
```
[TYPE] Brief description

- Detailed point 1
- Detailed point 2

Fixes #123 (if applicable)
```

**Commit Types:**
- `[feat]` - New feature
- `[fix]` - Bug fix
- `[docs]` - Documentation update
- `[test]` - Test addition/update
- `[perf]` - Performance improvement
- `[refactor]` - Code refactoring
- `[chore]` - Build, CI, dependencies

#### 4. Test Your Changes

```bash
# Run full test suite
./vendor/bin/pest

# Check code style
./vendor/bin/pint

# Run static analysis
./vendor/bin/phpstan analyse src

# Check architecture
./vendor/bin/pest --architecture
```

#### 5. Keep Your Branch Updated

```bash
# Fetch latest changes
git fetch upstream

# Rebase your branch
git rebase upstream/main

# If conflicts, resolve and continue
# git add .
# git rebase --continue
```

#### 6. Push and Create Pull Request

```bash
# Push your branch
git push origin feature/my-feature

# Go to GitHub and create Pull Request
# Fill out PR template
```

### Pull Request Guidelines

**PR Title**: Should be clear and descriptive
```
[TYPE] Brief description - relates to #issue_number
```

**PR Description** should include:
1. **What** - What does this change do?
2. **Why** - Why is this change needed?
3. **How** - How does it work?
4. **Testing** - How was it tested?
5. **Breaking Changes** - Are there any breaking changes?

**PR Checklist:**
- [ ] Tests pass locally (`./vendor/bin/pest`)
- [ ] Code style fixed (`./vendor/bin/pint`)
- [ ] Static analysis passes (`./vendor/bin/phpstan analyse src`)
- [ ] Documentation updated
- [ ] No unnecessary dependencies added
- [ ] Changelog entry added (if applicable)

**Example PR:**
```markdown
## Description
Adds webhook signature validation for enhanced security.

## Type of Change
- [x] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Related Issue
Fixes #42

## Changes Made
- Added VerifyWebhookSignature middleware
- Validates HMAC-SHA256 signatures
- Checks timestamp (5-minute window)

## How to Test
1. Configure webhook secret
2. Send test webhook
3. Signature validation should pass

## Testing
- [x] Added new tests in tests/Feature/WebhookTest.php
- [x] All existing tests pass
- [x] Code coverage maintained

## Breaking Changes
None

## Checklist
- [x] Tests pass
- [x] Code style fixed
- [x] Documentation updated
```

## Coding Standards

### File Structure
```php
<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Services;

use Illuminate\Support\Facades\Log;
use VerifyNow\Laravel\Models\Verification;

/**
 * Brief class description
 *
 * Longer description explaining the purpose and usage
 */
class MyClass
{
    // Implementation
}
```

### Type Hints
```php
// GOOD - Full type hints
public function process(string $id, int $retries = 3): array
{
    // implementation
}

// BAD - Missing type hints
public function process($id, $retries = 3)
{
    // implementation
}
```

### PHPDoc Comments
```php
/**
 * Process a verification request
 *
 * @param string $verificationId The verification ID
 * @param array<string, mixed> $data Additional data
 * @return array<string, mixed> Processing result
 *
 * @throws VerifyNowException If verification fails
 */
public function process(string $verificationId, array $data = []): array
{
    // implementation
}
```

### Naming Conventions
- **Classes**: PascalCase (VerifyNowService)
- **Methods**: camelCase (requestIDV)
- **Properties**: camelCase ($verificationId)
- **Constants**: SNAKE_CASE (API_KEY)
- **Methods returning bool**: starts with "is", "has", "can" (isVerified, hasAttempts)

### Error Handling
```php
try {
    $response = $this->api->request($endpoint);
} catch (GuzzleException $e) {
    Log::error('API request failed', ['error' => $e->getMessage()]);
    throw new VerifyNowException('Request failed', 0, $e);
}
```

## Testing Guidelines

### Write Tests For:
- New features
- Bug fixes
- Public methods
- Edge cases
- Error conditions

### Test Structure
```php
class VerificationTest extends TestCase
{
    /**
     * Test descriptive name of what you're testing
     *
     * @return void
     */
    public function test_verification_can_be_created(): void
    {
        // Arrange - set up test data
        $data = ['user_id' => 1, 'type' => 'idv'];
        
        // Act - perform the action
        $verification = Verification::create($data);
        
        // Assert - verify results
        $this->assertDatabaseHas('verifications', $data);
    }
}
```

### Good Test Practices
- One assertion per test (or related assertions)
- Clear, descriptive test names
- Use fixtures and factories
- Test both success and failure cases
- Mock external dependencies (HTTP, APIs)
- Isolate tests from each other

## Documentation

### Update Documentation For:
- New features
- API changes
- Bug fixes that affect usage
- Configuration options

### Documentation Files
- **README.md** - Overview and quick start
- **ARCHITECTURE.md** - Internal architecture
- **CONTRIBUTING.md** - This file
- **CHANGELOG.md** - Version history

## Release Process

### Version Bumping
1. Update version in `composer.json`
2. Update CHANGELOG.md with changes
3. Create release commit
4. Tag release: `git tag -a v1.0.1 -m "Release v1.0.1"`
5. Push: `git push && git push origin v1.0.1`

### Semantic Versioning
- **MAJOR.MINOR.PATCH**
- MAJOR: Breaking changes
- MINOR: New backward-compatible features
- PATCH: Bug fixes

## Getting Help

- **Issues**: Use GitHub issues for bugs and features
- **Discussions**: Use GitHub discussions for questions
- **Email**: support@verifinow.io
- **Documentation**: See ARCHITECTURE.md and README.md

## Recognition

Contributors will be recognized in:
- CHANGELOG.md
- GitHub contributors page
- Release notes

## License

By contributing to this project, you agree that your contributions will be licensed under the MIT License.

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs)
- [PHP Standards Recommendations](https://www.php-fig.org/psr/)
- [Semantic Versioning](https://semver.org/)
- [Conventional Commits](https://www.conventionalcommits.org/)

Thank you for contributing! 🎉

