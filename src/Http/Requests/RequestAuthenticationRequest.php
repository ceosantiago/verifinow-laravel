<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request Authentication Request Validation
 *
 * Validates facial recognition/authentication request data
 */
class RequestAuthenticationRequest extends FormRequest
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'verification_id' => ['required', 'string'],
            'device_type' => ['nullable', 'string', 'in:mobile,web,tablet'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'liveness_level' => ['nullable', 'string', 'in:low,medium,high'],
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
            'verification_id.required' => 'Verification ID is required for authentication',
            'device_type.in' => 'Device type must be one of: mobile, web, tablet',
            'liveness_level.in' => 'Liveness level must be one of: low, medium, high',
        ];
    }
}
