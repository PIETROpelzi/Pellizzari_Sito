<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceTelemetryRequest;
use App\Models\Alert;
use App\Models\Dispenser;
use App\Models\Medicine;
use App\Models\SensorLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

class DeviceTelemetryController extends Controller
{
    public function store(StoreDeviceTelemetryRequest $request): JsonResponse
    {
        /** @var Dispenser $dispenser */
        $dispenser = $request->attributes->get('dispenser');
        $validated = $request->validated();
        $recordedAt = isset($validated['recorded_at'])
            ? Carbon::parse($validated['recorded_at'])
            : now();

        $temperature = (float) $validated['temperature'];
        $humidity = (float) $validated['humidity'];
        $violations = $this->detectThresholdViolations(
            patientId: $dispenser->patient_id,
            temperature: $temperature,
            humidity: $humidity,
        );

        $sensorLog = SensorLog::query()->create([
            'dispenser_id' => $dispenser->id,
            'patient_id' => $dispenser->patient_id,
            'temperature' => $temperature,
            'humidity' => $humidity,
            'battery_level' => $validated['battery_level'] ?? null,
            'threshold_exceeded' => $violations->isNotEmpty(),
            'threshold_violations' => $violations->values()->all(),
            'recorded_at' => $recordedAt,
        ]);

        $dispenser->update([
            'last_seen_at' => $recordedAt,
            'is_online' => true,
        ]);

        foreach ($violations as $violation) {
            $alreadyOpen = Alert::query()
                ->where('patient_id', $dispenser->patient_id)
                ->where('dispenser_id', $dispenser->id)
                ->where('type', $violation['type'])
                ->open()
                ->where('triggered_at', '>=', now()->subHour())
                ->exists();

            if (! $alreadyOpen) {
                Alert::query()->create([
                    'patient_id' => $dispenser->patient_id,
                    'dispenser_id' => $dispenser->id,
                    'sensor_log_id' => $sensorLog->id,
                    'type' => $violation['type'],
                    'severity' => $violation['severity'],
                    'message' => $violation['message'],
                    'triggered_at' => $recordedAt,
                    'notified_caregiver' => false,
                    'notified_doctor' => false,
                ]);
            }
        }

        return response()->json([
            'message' => 'Telemetria acquisita.',
            'sensor_log_id' => $sensorLog->id,
            'threshold_exceeded' => $sensorLog->threshold_exceeded,
            'violations' => $violations->values(),
        ], 201);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function detectThresholdViolations(int $patientId, float $temperature, float $humidity): Collection
    {
        $medicines = Medicine::query()
            ->where('patient_id', $patientId)
            ->get([
                'minimum_temperature',
                'maximum_temperature',
                'minimum_humidity',
                'maximum_humidity',
            ]);

        if ($medicines->isEmpty()) {
            return collect();
        }

        $violations = collect();
        $minTemperature = $medicines->pluck('minimum_temperature')->filter(static fn ($value): bool => $value !== null)->max();
        $maxTemperature = $medicines->pluck('maximum_temperature')->filter(static fn ($value): bool => $value !== null)->min();
        $minHumidity = $medicines->pluck('minimum_humidity')->filter(static fn ($value): bool => $value !== null)->max();
        $maxHumidity = $medicines->pluck('maximum_humidity')->filter(static fn ($value): bool => $value !== null)->min();

        if ($minTemperature !== null && $temperature < (float) $minTemperature) {
            $violations->push([
                'type' => Alert::TYPE_TEMPERATURE,
                'severity' => 'High',
                'message' => 'Temperatura troppo bassa: '.$temperature.'°C',
            ]);
        }

        if ($maxTemperature !== null && $temperature > (float) $maxTemperature) {
            $violations->push([
                'type' => Alert::TYPE_TEMPERATURE,
                'severity' => 'High',
                'message' => 'Temperatura troppo alta: '.$temperature.'°C',
            ]);
        }

        if ($minHumidity !== null && $humidity < (float) $minHumidity) {
            $violations->push([
                'type' => Alert::TYPE_HUMIDITY,
                'severity' => 'Medium',
                'message' => 'Umidita troppo bassa: '.$humidity.'%',
            ]);
        }

        if ($maxHumidity !== null && $humidity > (float) $maxHumidity) {
            $violations->push([
                'type' => Alert::TYPE_HUMIDITY,
                'severity' => 'High',
                'message' => 'Umidita troppo alta: '.$humidity.'%',
            ]);
        }

        return $violations;
    }
}
