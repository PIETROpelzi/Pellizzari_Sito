<?php

namespace App\Http\Requests;

use App\Models\DoseLog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDeviceDoseLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'therapy_plan_id' => ['nullable', 'integer', 'exists:therapy_plans,id'],
            'medicine_id' => ['nullable', 'integer', 'exists:medicines,id'],
            'status' => [
                'required',
                Rule::in([
                    DoseLog::STATUS_PENDING,
                    DoseLog::STATUS_DISPENSED,
                    DoseLog::STATUS_TAKEN,
                    DoseLog::STATUS_MISSED,
                    DoseLog::STATUS_SNOOZED,
                    DoseLog::STATUS_SKIPPED,
                ]),
            ],
            'scheduled_for' => ['nullable', 'date'],
            'event_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

