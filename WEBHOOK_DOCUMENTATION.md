# VerifyNow Webhook Documentation

## Overview

This document describes the webhook callbacks received from VerifyNow after users complete identity verification in the portal. Webhooks allow your application to be notified in real-time when verification status changes.

## Webhook Endpoint

Your application should expose a webhook endpoint to receive POST requests from VerifyNow:

```
POST /api/webhooks/verifynow
```

## Authentication

Webhooks are sent with an API key in the header:

```
Authorization: api-key {your-api-key}
X-Webhook-Signature: {hmac-signature}
```

## Webhook Response Structure

### Successful Verification Webhook

When a user completes verification successfully, VerifyNow sends a webhook with this structure:

```json
{
  "event": "verification.completed",
  "timestamp": "2024-12-26T10:30:45Z",
  "data": {
    "userID": "019b5d2f-823f-78e7-b4e6-a59cb9c16447",
    "status": "approved",
    "result": "verified",
    "confidence_score": 0.98,
    "verification_type": "idv",
    "document_type": "passport",
    "country": "US",
    "verified_data": {
      "firstName": "John",
      "lastName": "Doe",
      "dateOfBirth": "1990-05-15",
      "expiryDate": "2030-12-31"
    },
    "metadata": {
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "verification_duration_seconds": 125,
      "retry_count": 0
    }
  }
}
```

### Failed Verification Webhook

When verification fails, the webhook structure is:

```json
{
  "event": "verification.failed",
  "timestamp": "2024-12-26T10:30:45Z",
  "data": {
    "userID": "019b5d2f-823f-78e7-b4e6-a59cb9c16447",
    "status": "rejected",
    "result": "failed",
    "failure_reason": "document_expired",
    "failure_code": "DOC_EXPIRED",
    "verification_type": "idv",
    "country": "US",
    "metadata": {
      "ip_address": "192.168.1.100",
      "user_agent": "Mozilla/5.0...",
      "verification_duration_seconds": 45,
      "retry_count": 1
    }
  }
}
```

### Expired Verification Webhook

When a verification link expires without completion:

```json
{
  "event": "verification.expired",
  "timestamp": "2024-12-26T10:30:45Z",
  "data": {
    "userID": "019b5d2f-823f-78e7-b4e6-a59cb9c16447",
    "status": "expired",
    "result": "timeout",
    "verification_type": "idv",
    "country": "US",
    "expires_at": "2024-12-26T09:30:45Z",
    "metadata": {
      "expiration_reason": "link_expired",
      "days_expired": 1
    }
  }
}
```

## Field Descriptions

### Event Types

| Event | Description |
|-------|-------------|
| `verification.completed` | User successfully completed verification |
| `verification.failed` | User failed verification (document issues, face mismatch, etc.) |
| `verification.expired` | Verification link expired without completion |
| `verification.rejected` | Admin manually rejected the verification |
| `verification.resubmitted` | User resubmitted verification after failure |

### Status Values

| Status | Meaning |
|--------|---------|
| `approved` | Verification passed all checks |
| `rejected` | Verification failed checks |
| `expired` | Link expired before completion |
| `pending` | Verification in progress |
| `manual_review` | Requires manual verification review |

### Result Values

| Result | Description |
|--------|-------------|
| `verified` | User identity confirmed |
| `failed` | Identity verification failed |
| `timeout` | User didn't complete within time limit |
| `incomplete` | User started but didn't finish |
| `fraud_detected` | Fraudulent activity detected |

### Failure Codes

| Code | Description |
|------|-------------|
| `DOC_EXPIRED` | Document has expired |
| `DOC_INVALID` | Document is invalid or forged |
| `FACE_MISMATCH` | Face doesn't match document photo |
| `DATA_MISMATCH` | Personal data doesn't match document |
| `POOR_QUALITY` | Document image quality too low |
| `UNSUPPORTED_DOC` | Document type not supported |
| `MANUAL_REVIEW` | Document requires manual review |
| `DUPLICATE` | Duplicate verification attempt |
| `UNDERAGE` | User is underage |

### Verification Types

| Type | Description |
|------|-------------|
| `idv` | Identity Verification (primary ID document) |
| `age_verification` | Age verification only |
| `liveness` | Liveness check (face selfie) |
| `document_verification` | Secondary document verification |

### Document Types

Common document types returned in webhooks:

- `passport`
- `driving_license`
- `national_id`
- `visa`
- `residence_permit`
- `utility_bill` (address verification)
- `bank_statement` (address verification)

## Implementation Example

### Laravel Webhook Handler

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Verification;

class VerifyNowWebhookController extends Controller
{
    /**
     * Handle incoming VerifyNow webhook
     */
    public function handle(Request $request)
    {
        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = $request->input('event');
        $data = $request->input('data');
        $userID = $data['userID'];

        // Find the user by verification ID
        $verification = Verification::where('verification_id', $userID)->first();
        
        if (!$verification) {
            return response()->json(['error' => 'Verification not found'], 404);
        }

        // Handle different event types
        switch ($event) {
            case 'verification.completed':
                $this->handleVerificationCompleted($verification, $data);
                break;
            case 'verification.failed':
                $this->handleVerificationFailed($verification, $data);
                break;
            case 'verification.expired':
                $this->handleVerificationExpired($verification, $data);
                break;
            default:
                \Log::warning("Unknown webhook event: {$event}");
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle successful verification
     */
    private function handleVerificationCompleted(Verification $verification, array $data)
    {
        $verification->update([
            'status' => 'completed',
            'result' => $data['result'],
            'confidence_score' => $data['confidence_score'] ?? null,
            'document_type' => $data['document_type'] ?? null,
            'metadata' => array_merge(
                $verification->metadata ?? [],
                [
                    'verified_data' => $data['verified_data'] ?? [],
                    'webhook_timestamp' => $data['timestamp'],
                ]
            ),
        ]);

        // Mark user as verified if confidence is high
        if (($data['confidence_score'] ?? 0) > 0.95) {
            $verification->user->update(['is_verified' => true]);
            
            // Trigger welcome email or additional onboarding
            event(new UserVerified($verification->user));
        }

        \Log::info("Verification completed for user {$verification->user_id}");
    }

    /**
     * Handle failed verification
     */
    private function handleVerificationFailed(Verification $verification, array $data)
    {
        $verification->update([
            'status' => 'failed',
            'result' => $data['result'],
            'metadata' => array_merge(
                $verification->metadata ?? [],
                [
                    'failure_reason' => $data['failure_reason'] ?? null,
                    'failure_code' => $data['failure_code'] ?? null,
                    'webhook_timestamp' => $data['timestamp'],
                ]
            ),
        ]);

        // Send notification to user about failure
        $verification->user->notify(new VerificationFailed($data['failure_reason'] ?? 'Unknown'));

        \Log::warning("Verification failed for user {$verification->user_id}: {$data['failure_code']}");
    }

    /**
     * Handle expired verification
     */
    private function handleVerificationExpired(Verification $verification, array $data)
    {
        $verification->update([
            'status' => 'expired',
            'result' => 'timeout',
            'metadata' => array_merge(
                $verification->metadata ?? [],
                [
                    'webhook_timestamp' => $data['timestamp'],
                ]
            ),
        ]);

        \Log::info("Verification expired for user {$verification->user_id}");
    }

    /**
     * Verify webhook signature using HMAC
     */
    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Webhook-Signature');
        $payload = $request->getContent();
        
        $expectedSignature = hash_hmac(
            'sha256',
            $payload,
            config('services.verifynow.webhook_secret')
        );

        return hash_equals($expectedSignature, $signature ?? '');
    }
}
```

### Route Configuration

```php
// routes/api.php
Route::post('/webhooks/verifynow', 'VerifyNowWebhookController@handle')
    ->name('webhooks.verifynow')
    ->middleware('api');
```

## Webhook Security

### Best Practices

1. **Verify Signature**: Always verify the webhook signature using HMAC SHA-256
2. **Validate API Key**: Check that the API key in the Authorization header matches your secret
3. **HTTPS Only**: Only accept webhooks via secure HTTPS connections
4. **Idempotency**: Implement idempotent handlers (webhooks may be retried)
5. **Logging**: Log all webhook events for audit trails
6. **Timeout Handling**: Respond quickly; implement asynchronous processing if needed

### Signature Verification

```php
$signature = hash_hmac(
    'sha256',
    $request->getContent(),
    env('VERIFYNOW_WEBHOOK_SECRET')
);

if (!hash_equals($signature, $request->header('X-Webhook-Signature'))) {
    return response()->json(['error' => 'Invalid signature'], 401);
}
```

## Retry Policy

VerifyNow implements the following webhook retry policy:

- **Initial Attempt**: Immediate delivery
- **1st Retry**: 1 minute after first attempt
- **2nd Retry**: 5 minutes after first attempt
- **3rd Retry**: 15 minutes after first attempt
- **4th Retry**: 1 hour after first attempt
- **5th Retry**: 4 hours after first attempt

After 5 retries, the webhook is marked as failed.

## Response Requirements

Your webhook endpoint must:

1. **Return Status 200-299**: Indicate successful receipt
2. **Respond Quickly**: Within 5 seconds
3. **Return JSON**: Valid JSON response

**Successful Response:**
```json
{
  "success": true,
  "message": "Webhook processed"
}
```

**Error Response (will trigger retry):**
```json
{
  "error": "Processing failed",
  "code": "INTERNAL_ERROR"
}
```

## Testing Webhooks

### Manual Test

Use cURL to simulate a webhook:

```bash
curl -X POST http://localhost:8000/api/webhooks/verifynow \
  -H "Content-Type: application/json" \
  -H "Authorization: api-key your-api-key" \
  -d '{
    "event": "verification.completed",
    "timestamp": "2024-12-26T10:30:45Z",
    "data": {
      "userID": "test-user-id",
      "status": "approved",
      "result": "verified",
      "confidence_score": 0.98
    }
  }'
```

### Local Testing with ngrok

1. Install ngrok: `brew install ngrok`
2. Expose local server: `ngrok http 8000`
3. Add webhook URL to VerifyNow dashboard: `https://your-ngrok-url.ngrok.io/api/webhooks/verifynow`
4. Trigger test webhooks from VerifyNow dashboard

## Webhook Status Tracking

Database schema for tracking webhooks:

```php
Schema::create('webhook_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type');
    $table->string('user_id')->nullable();
    $table->string('verification_id');
    $table->json('payload');
    $table->string('status'); // 'success', 'failed', 'processing'
    $table->text('error_message')->nullable();
    $table->integer('attempt_count')->default(1);
    $table->timestamp('processed_at')->nullable();
    $table->timestamps();
});
```

## Common Issues

### Issue: Webhook Not Received

**Possible Causes:**
- Invalid webhook URL
- Firewall blocking requests
- HTTPS certificate issues
- DNS resolution problems

**Solution:**
1. Check webhook URL in VerifyNow dashboard
2. Verify HTTPS is enabled
3. Check firewall/security group settings
4. Test with ngrok

### Issue: Signature Verification Fails

**Possible Causes:**
- Wrong webhook secret
- Request body modified
- Incorrect hash algorithm

**Solution:**
1. Verify webhook secret matches config
2. Use raw request body, not parsed JSON
3. Ensure SHA-256 algorithm

### Issue: Duplicate Processing

**Possible Causes:**
- Webhook retries without idempotency
- Multiple webhook endpoints

**Solution:**
1. Implement idempotent handlers
2. Store webhook IDs to prevent duplicates
3. Use database transactions

## Monitoring & Alerts

### Health Check Query

```php
// Check for failed webhooks in last 24 hours
$failed = WebhookLog::where('status', 'failed')
    ->where('created_at', '>', now()->subDay())
    ->count();

if ($failed > 0) {
    alert("$failed webhook failures in last 24 hours");
}
```

### Metrics to Monitor

- Webhook receipt time
- Processing time
- Success rate
- Error types
- Retry counts
- User impact (unverified accounts)

## Support

For webhook issues, contact VerifyNow support:
- Email: support@verifynow.io
- Documentation: https://docs.verifynow.io/webhooks
- Status Page: https://status.verifynow.io
