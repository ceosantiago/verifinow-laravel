# Extending VerifyNow Laravel Package

Guide to extending the VerifyNow Laravel Package for custom use cases.

## Table of Contents

1. [Custom Services](#custom-services)
2. [Custom Models](#custom-models)
3. [Custom Events & Listeners](#custom-events--listeners)
4. [Custom Middleware](#custom-middleware)
5. [Custom Requests](#custom-requests)
6. [Database Extensions](#database-extensions)
7. [Service Provider Extension](#service-provider-extension)
8. [Real-World Examples](#real-world-examples)

## Custom Services

### Extending IDV Service

```php
// app/Services/VerifyNow/CustomIDVService.php
namespace App\Services\VerifyNow;

use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\VerifyNowService;

class CustomIDVService extends IDVService {
    
    /**
     * Request IDV with additional validation
     */
    public function requestWithValidation(array $data): array {
        // Custom validation logic
        $this->validateCountry($data['country']);
        $this->validateAge($data['date_of_birth'] ?? null);
        
        // Call parent method
        return parent::request($data);
    }
    
    /**
     * Validate country code
     */
    private function validateCountry(string $country): void {
        $allowed = ['US', 'GB', 'CA', 'AU'];
        
        if (!in_array($country, $allowed)) {
            throw new \InvalidArgumentException("Country {$country} not supported");
        }
    }
    
    /**
     * Validate age requirement
     */
    private function validateAge(?string $dob): void {
        if (!$dob) return;
        
        $age = now()->diffInYears($dob);
        if ($age < 18) {
            throw new \InvalidArgumentException("User must be 18+ years old");
        }
    }
}
```

### Extending Authentication Service

```php
// app/Services/VerifyNow/EnhancedAuthenticationService.php
namespace App\Services\VerifyNow;

use VerifyNow\Laravel\Services\AuthenticationService;

class EnhancedAuthenticationService extends AuthenticationService {
    
    /**
     * Request authentication with liveness level
     */
    public function requestWithLiveness(
        array $data,
        string $livenessLevel = 'medium'
    ): array {
        // Add liveness level
        $data['liveness_level'] = $livenessLevel;
        
        return parent::request($data);
    }
    
    /**
     * Request re-authentication for high-value actions
     */
    public function requestForHighValueAction(
        array $data,
        string $actionType
    ): array {
        // Enforce high liveness level for sensitive actions
        return $this->requestWithLiveness(
            $data,
            'high'
        );
    }
    
    /**
     * Get comprehensive authentication score
     */
    public function getAuthenticationScore(string $verificationId): float {
        $status = $this->checkStatus($verificationId);
        
        $confidenceScore = $status['confidence_score'] ?? 0;
        $livenessScore = $status['liveness_score'] ?? 0;
        $faceMatchScore = $status['face_match_score'] ?? 0;
        
        // Weighted average
        return (
            $confidenceScore * 0.4 +
            $livenessScore * 0.3 +
            $faceMatchScore * 0.3
        );
    }
}
```

### Creating Specialized Services

```php
// app/Services/VerifyNow/BatchVerificationService.php
namespace App\Services\VerifyNow;

use Illuminate\Support\Collection;
use VerifyNow\Laravel\Services\IDVService;

class BatchVerificationService {
    
    public function __construct(private IDVService $idv) {}
    
    /**
     * Request verification for multiple users
     */
    public function requestBatch(array $users): Collection {
        return collect($users)->map(function ($user) {
            return $this->idv->request([
                'user_id' => $user['id'],
                'country' => $user['country'],
            ]);
        });
    }
    
    /**
     * Check status of multiple verifications
     */
    public function checkBatchStatus(array $verificationIds): Collection {
        return collect($verificationIds)->map(function ($id) {
            return [
                'id' => $id,
                'status' => $this->idv->checkStatus($id),
            ];
        });
    }
}
```

### Register Custom Service

```php
// app/Providers/AppServiceProvider.php
namespace App\Providers;

use App\Services\VerifyNow\CustomIDVService;
use App\Services\VerifyNow\EnhancedAuthenticationService;
use Illuminate\Support\ServiceProvider;
use VerifyNow\Laravel\Services\IDVService;
use VerifyNow\Laravel\Services\AuthenticationService;

class AppServiceProvider extends ServiceProvider {
    
    public function register(): void {
        // Replace default services with custom ones
        $this->app->bind(IDVService::class, CustomIDVService::class);
        $this->app->bind(AuthenticationService::class, EnhancedAuthenticationService::class);
    }
}
```

## Custom Models

### Extending Verification Model

```php
// app/Models/CustomVerification.php
namespace App\Models;

use VerifyNow\Laravel\Models\Verification;

class CustomVerification extends Verification {
    
    /**
     * Get rejectable reasons
     */
    public function getRejectionReasonAttribute(): ?string {
        return $this->metadata['rejection_reason'] ?? null;
    }
    
    /**
     * Get verification attempt count
     */
    public function getAttemptCountAttribute(): int {
        return $this->attempts()->count();
    }
    
    /**
     * Scope: Get recent verifications
     */
    public function scopeRecent($query, int $days = 30) {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    
    /**
     * Scope: Get by verification type
     */
    public function scopeOfType($query, string $type) {
        return $query->where('type', $type);
    }
    
    /**
     * Scope: Get pending verifications
     */
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
    
    /**
     * Get related user
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
}
```

### Custom User Authentication Model

```php
// app/Models/CustomUserAuthentication.php
namespace App\Models;

use VerifyNow\Laravel\Models\UserAuthentication;

class CustomUserAuthentication extends UserAuthentication {
    
    /**
     * Check if authentication meets requirements
     */
    public function meetsRequirements(float $minConfidence = 90): bool {
        return $this->confidence_score >= $minConfidence &&
               $this->liveness_score >= 70 &&
               $this->face_match_score >= 80;
    }
    
    /**
     * Get authentication quality assessment
     */
    public function getQualityAssessmentAttribute(): string {
        if ($this->confidence_score >= 95) {
            return 'excellent';
        } elseif ($this->confidence_score >= 85) {
            return 'good';
        } elseif ($this->confidence_score >= 70) {
            return 'fair';
        }
        return 'poor';
    }
    
    /**
     * Scope: Get high-quality authentications
     */
    public function scopeHighQuality($query) {
        return $query->where('confidence_score', '>=', 90);
    }
}
```

## Custom Events & Listeners

### Custom Events

```php
// app/Events/VerifyNow/VerificationApprovedEvent.php
namespace App\Events\VerifyNow;

use App\Models\Verification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationApprovedEvent {
    use Dispatchable, SerializesModels;
    
    public function __construct(public Verification $verification) {}
}

// app/Events/VerifyNow/VerificationRejectedEvent.php
namespace App\Events\VerifyNow;

use App\Models\Verification;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationRejectedEvent {
    use Dispatchable, SerializesModels;
    
    public function __construct(
        public Verification $verification,
        public string $reason
    ) {}
}
```

### Custom Listeners

```php
// app/Listeners/VerifyNow/GrantVerifiedBadge.php
namespace App\Listeners\VerifyNow;

use App\Events\VerifyNow\VerificationApprovedEvent;

class GrantVerifiedBadge {
    
    public function handle(VerificationApprovedEvent $event): void {
        $user = $event->verification->user;
        
        // Grant verified badge
        $user->verified_at = now();
        $user->save();
        
        // Add verified role
        $user->assignRole('verified');
    }
}

// app/Listeners/VerifyNow/NotifyVerificationApproved.php
namespace App\Listeners\VerifyNow;

use App\Events\VerifyNow\VerificationApprovedEvent;
use App\Notifications\VerificationApprovedNotification;

class NotifyVerificationApproved {
    
    public function handle(VerificationApprovedEvent $event): void {
        $user = $event->verification->user;
        
        $user->notify(new VerificationApprovedNotification(
            $event->verification
        ));
    }
}

// app/Listeners/VerifyNow/LogVerificationFailure.php
namespace App\Listeners\VerifyNow;

use App\Events\VerifyNow\VerificationRejectedEvent;
use Illuminate\Support\Facades\Log;

class LogVerificationFailure {
    
    public function handle(VerificationRejectedEvent $event): void {
        Log::warning('Verification rejected', [
            'user_id' => $event->verification->user_id,
            'verification_id' => $event->verification->verification_id,
            'reason' => $event->reason,
        ]);
    }
}
```

### Register Events & Listeners

```php
// app/Providers/EventServiceProvider.php
namespace App\Providers;

use App\Events\VerifyNow\VerificationApprovedEvent;
use App\Events\VerifyNow\VerificationRejectedEvent;
use App\Listeners\VerifyNow\GrantVerifiedBadge;
use App\Listeners\VerifyNow\NotifyVerificationApproved;
use App\Listeners\VerifyNow\LogVerificationFailure;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use VerifyNow\Laravel\Events\VerificationCompleted;
use VerifyNow\Laravel\Events\VerificationFailed;

class EventServiceProvider extends ServiceProvider {
    
    protected $listen = [
        // Package events
        VerificationCompleted::class => [
            'App\Listeners\VerifyNow\DispatchApprovedEvent',
        ],
        VerificationFailed::class => [
            'App\Listeners\VerifyNow\DispatchRejectedEvent',
        ],
        
        // Custom events
        VerificationApprovedEvent::class => [
            GrantVerifiedBadge::class,
            NotifyVerificationApproved::class,
        ],
        VerificationRejectedEvent::class => [
            LogVerificationFailure::class,
        ],
    ];
}
```

## Custom Middleware

### Extended Route Protection

```php
// app/Http/Middleware/RequireHighLevelVerification.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireHighLevelVerification {
    
    public function handle(Request $request, Closure $next, string $level = 'high') {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        $lastAuth = $user->lastSuccessfulAuthentication();
        
        if (!$lastAuth) {
            return response()->json(['message' => 'Authentication required'], 403);
        }
        
        $score = $this->calculateScore($lastAuth);
        $required = $this->getRequiredScore($level);
        
        if ($score < $required) {
            return response()->json([
                'message' => 'Verification level insufficient',
                'required_level' => $level,
                'current_score' => $score,
            ], 403);
        }
        
        return $next($request);
    }
    
    private function calculateScore($authentication): float {
        return (
            $authentication->confidence_score * 0.4 +
            $authentication->liveness_score * 0.3 +
            $authentication->face_match_score * 0.3
        );
    }
    
    private function getRequiredScore(string $level): float {
        return match($level) {
            'low' => 60,
            'medium' => 75,
            'high' => 90,
            default => 75,
        };
    }
}

// Register in HTTP Kernel
// protected $routeMiddleware = [
//     'verifinow.high' => \App\Http\Middleware\RequireHighLevelVerification::class,
// ];
```

### Usage

```php
// routes/api.php
Route::middleware('verifinow.high:high')->group(function () {
    Route::post('/sensitive-action', [ActionController::class, 'store']);
});
```

## Custom Requests

### Extended Validation

```php
// app/Http/Requests/VerifyNow/AdvancedIDVRequest.php
namespace App\Http\Requests\VerifyNow;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ValidPhoneNumber;

class AdvancedIDVRequest extends FormRequest {
    
    public function authorize(): bool {
        return true;
    }
    
    public function rules(): array {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'country' => ['required', 'string', 'size:2', 'uppercase'],
            'document_type' => ['required', 'string', 'in:passport,driver_license,national_id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:' . now()->subYears(18)],
            'phone' => ['required', new ValidPhoneNumber()],
            'agree_terms' => ['required', 'accepted'],
        ];
    }
    
    public function messages(): array {
        return [
            'date_of_birth.before_or_equal' => 'You must be at least 18 years old',
            'agree_terms.accepted' => 'You must agree to the terms',
        ];
    }
}
```

## Database Extensions

### Adding Custom Columns to Verification

```php
// database/migrations/2025_01_01_000004_add_custom_fields_to_verifications.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::table('verifications', function (Blueprint $table) {
            $table->string('rejection_reason')->nullable();
            $table->string('reviewer_notes')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedSmallInteger('revision_count')->default(0);
        });
    }
    
    public function down(): void {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropColumn([
                'rejection_reason',
                'reviewer_notes',
                'reviewed_by',
                'reviewed_at',
                'revision_count',
            ]);
        });
    }
};
```

### Creating Audit Trail Table

```php
// database/migrations/2025_01_01_000005_create_verification_audits_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void {
        Schema::create('verification_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('verifications')->onDelete('cascade');
            $table->string('action');
            $table->json('changes')->nullable();
            $table->string('performed_by')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('verification_audits');
    }
};
```

## Service Provider Extension

### Custom Service Provider

```php
// app/Providers/VerifyNowServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\VerifyNow\CustomIDVService;
use App\Services\VerifyNow\BatchVerificationService;
use VerifyNow\Laravel\Services\IDVService;

class VerifyNowServiceProvider extends ServiceProvider {
    
    public function register(): void {
        // Replace services
        $this->app->bind(IDVService::class, CustomIDVService::class);
        
        // Register custom services
        $this->app->singleton(BatchVerificationService::class, function ($app) {
            return new BatchVerificationService(
                $app->make(CustomIDVService::class)
            );
        });
    }
    
    public function boot(): void {
        // Publish custom migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'verifinow-custom-migrations');
        
        // Register custom commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\VerifyNowSync::class,
            ]);
        }
    }
}
```

## Real-World Examples

### Example 1: Team Verification Workflow

```php
// app/Http/Controllers/TeamVerificationController.php
namespace App\Http\Controllers;

use App\Models\Team;
use VerifyNow\Laravel\Facades\VerifyNow;
use Illuminate\Http\Request;

class TeamVerificationController extends Controller {
    
    /**
     * Request team manager verification
     */
    public function requestManagerVerification(Team $team) {
        $manager = $team->owner;
        
        $response = VerifyNow::requestIDV([
            'user_id' => $manager->id,
            'country' => $team->country,
            'document_type' => 'driver_license',
        ]);
        
        // Store verification reference
        $team->verification_id = $response['verification_id'];
        $team->save();
        
        return response()->json([
            'verification_id' => $response['verification_id'],
            'status' => 'pending',
        ]);
    }
    
    /**
     * Check team verification status
     */
    public function checkVerificationStatus(Team $team) {
        if (!$team->verification_id) {
            return response()->json([
                'message' => 'No verification in progress',
            ], 404);
        }
        
        $status = VerifyNow::checkVerificationStatus($team->verification_id);
        
        if ($status['status'] === 'completed') {
            if ($status['result'] === 'approved') {
                $team->markAsVerified();
                return response()->json(['status' => 'approved']);
            } else {
                return response()->json(['status' => 'rejected']);
            }
        }
        
        return response()->json($status);
    }
}
```

### Example 2: Tournament Age Verification

```php
// app/Services/TournamentRegistrationService.php
namespace App\Services;

use App\Models\Tournament;
use App\Models\User;
use VerifyNow\Laravel\Services\IDVService;

class TournamentRegistrationService {
    
    public function __construct(private IDVService $idv) {}
    
    /**
     * Verify player age for tournament
     */
    public function verifyPlayerAge(User $user, Tournament $tournament): array {
        $minimumAge = $tournament->minimum_age;
        
        return $this->idv->request([
            'user_id' => $user->id,
            'country' => $user->country,
            'date_of_birth_check' => true,
            'minimum_age' => $minimumAge,
        ]);
    }
    
    /**
     * Check if player meets tournament requirements
     */
    public function playerMeetsRequirements(User $user, Tournament $tournament): bool {
        $verification = $user->verifications()
            ->where('type', 'age_verification')
            ->where('result', 'approved')
            ->where('created_at', '>=', now()->subYear())
            ->first();
        
        if (!$verification) {
            return false;
        }
        
        // Verify against tournament minimum age
        $metadata = $verification->metadata;
        return ($metadata['age'] ?? 0) >= $tournament->minimum_age;
    }
}
```

### Example 3: KYC Compliance System

```php
// app/Services/KYCComplianceService.php
namespace App\Services;

use App\Models\User;
use VerifyNow\Laravel\Facades\VerifyNow;

class KYCComplianceService {
    
    /**
     * Complete KYC verification process
     */
    public function completeKYC(User $user, array $data): array {
        // Step 1: IDV
        $idvResponse = VerifyNow::requestIDV([
            'user_id' => $user->id,
            'country' => $data['country'],
            'document_type' => $data['document_type'],
        ]);
        
        // Step 2: Facial Authentication
        $authResponse = VerifyNow::requestAuthentication([
            'user_id' => $user->id,
            'verification_id' => $idvResponse['verification_id'],
        ]);
        
        return [
            'idv_id' => $idvResponse['verification_id'],
            'auth_id' => $authResponse['authentication_id'],
            'status' => 'pending',
        ];
    }
    
    /**
     * Check if user is KYC verified
     */
    public function isKYCVerified(User $user): bool {
        $idvVerified = $user->verifications()
            ->where('type', 'idv')
            ->where('result', 'approved')
            ->exists();
        
        $authVerified = $user->authentications()
            ->where('result', 'approved')
            ->exists();
        
        return $idvVerified && $authVerified;
    }
}
```

---

These examples provide templates for common extension scenarios. Adapt them to your specific use case.

