<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Check Verification Request Validation
 *
 * Validates verification status check request data
 */
class CheckVerificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'verification_id' => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Get custom messages for validation rules
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'verification_id.required' => 'Verification ID is required',
            'verification_id.min' => 'Verification ID appears to be invalid',
        ];
    }
}
