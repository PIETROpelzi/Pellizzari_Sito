<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dispenser;
use App\Models\TherapyPlan;
use Illuminate\Http\JsonResponse;

class DevicePlanController extends Controller
{
    public function index(): JsonResponse
    {
        /** @var Dispenser $dispenser */
        $dispenser = request()->attributes->get('dispenser');

        $plans = TherapyPlan::query()
            ->where('patient_id', $dispenser->patient_id)
            ->where('is_active', true)
            ->whereDate('starts_on', '<=', now())
            ->where(static function ($query): void {
                $query->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', now());
            })
            ->with([
                'medicine:id,name,description,image_url',
                'schedules:id,therapy_plan_id,scheduled_time,week_days,timezone',
            ])
            ->get()
            ->map(static function (TherapyPlan $plan): array {
                return [
                    'id' => $plan->id,
                    'medicine' => [
                        'id' => $plan->medicine->id,
                        'name' => $plan->medicine->name,
                        'description' => $plan->medicine->description,
                        'image_url' => $plan->medicine->image_url,
                    ],
                    'dose_amount' => $plan->dose_amount,
                    'dose_unit' => $plan->dose_unit,
                    'instructions' => $plan->instructions,
                    'starts_on' => $plan->starts_on?->toDateString(),
                    'ends_on' => $plan->ends_on?->toDateString(),
                    'schedules' => $plan->schedules
                        ->map(static fn ($schedule): array => [
                            'time' => substr((string) $schedule->scheduled_time, 0, 5),
                            'week_days' => $schedule->week_days ?? [],
                            'timezone' => $schedule->timezone,
                        ])
                        ->values(),
                ];
            })
            ->values();

        return response()->json([
            'device_uid' => $dispenser->device_uid,
            'patient_id' => $dispenser->patient_id,
            'plans' => $plans,
            'generated_at' => now()->toIso8601String(),
        ]);
    }
}
