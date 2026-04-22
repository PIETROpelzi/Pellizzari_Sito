<?php

namespace App\Http\Requests;

use App\Models\User;
use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::Admin, UserRole::Doctor) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $patient */
        $patient = $this->route('patient');
        $isAdmin = $this->user()?->hasRole(UserRole::Admin) ?? false;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$patient->id],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['nullable', 'date'],
            'is_active' => ['required', 'boolean'],
            'doctor_ids' => [
                Rule::requiredIf($isAdmin),
                Rule::prohibitedIf(! $isAdmin),
                'array',
                'min:1',
            ],
            'doctor_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', UserRole::Doctor->value)->where('is_active', true),
            ],
            'caregiver_ids' => ['nullable', 'array'],
            'caregiver_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('users', 'id')->where('role', UserRole::Caregiver->value)->where('is_active', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'doctor_ids' => 'dottore assegnato',
            'caregiver_ids' => 'familiari assegnati',
        ];
    }
}
