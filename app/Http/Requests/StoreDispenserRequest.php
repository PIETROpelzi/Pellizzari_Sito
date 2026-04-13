<?php

namespace App\Http\Requests;

use App\UserRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreDispenserRequest extends FormRequest
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
        return [
            'patient_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:120'],
            'device_uid' => ['required', 'string', 'max:120', 'unique:dispensers,device_uid'],
            'api_token' => ['nullable', 'string', 'max:120', 'unique:dispensers,api_token'],
            'mqtt_base_topic' => ['nullable', 'string', 'max:180'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
