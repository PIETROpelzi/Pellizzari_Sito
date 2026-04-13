<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceDoseLogRequest;
use App\Models\Alert;
use App\Models\Dispenser;
use App\Models\DoseLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class DeviceDoseLogController extends Controller
{
    public function store(StoreDeviceDoseLogRequest $request): JsonResponse
    {
        /** @var Dispenser $dispenser */
        $dispenser = $request->attributes->get('dispenser');
        $validated = $request->validated();
        $eventAt = isset($validated['event_at']) ? Carbon::parse($validated['event_at']) : now();

        $doseLog = DoseLog::query()->create([
            'patient_id' => $dispenser->patient_id,
            'dispenser_id' => $dispenser->id,
            'therapy_plan_id' => $validated['therapy_plan_id'] ?? null,
            'medicine_id' => $validated['medicine_id'] ?? null,
            'status' => $validated['status'],
            'source' => 'Device',
            'scheduled_for' => $validated['scheduled_for'] ?? null,
            'event_at' => $eventAt,
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($doseLog->status === DoseLog::STATUS_MISSED) {
            Alert::query()->create([
                'patient_id' => $dispenser->patient_id,
                'dispenser_id' => $dispenser->id,
                'dose_log_id' => $doseLog->id,
                'type' => Alert::TYPE_MISSED_DOSE,
                'severity' => 'High',
                'message' => 'Dose non assunta dal paziente.',
                'triggered_at' => $eventAt,
            ]);
        }

        return response()->json([
            'message' => 'Evento dose registrato.',
            'dose_log_id' => $doseLog->id,
        ], 201);
    }
}
