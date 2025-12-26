<?php

declare(strict_types=1);

namespace VerifyNow\Laravel\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request IDV Request Validation
 *
 * Validates IDV verification request data
 */
class RequestIDVRequest extends FormRequest
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
            'country' => ['required', 'string', 'size:2', 'uppercase'],
            'document_type' => ['nullable', 'string', 'in:passport,driver_license,national_id,visa'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date', 'before:' . now()->subYears(18)->format('Y-m-d')],
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
            'country.size' => 'Country code must be 2 characters (e.g., US, GB)',
            'country.uppercase' => 'Country code must be uppercase',
            'date_of_birth.before' => 'User must be at least 18 years old',
        ];
    }
}
