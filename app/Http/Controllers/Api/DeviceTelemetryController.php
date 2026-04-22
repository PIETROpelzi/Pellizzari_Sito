<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceTelemetryRequest;
use App\Models\Dispenser;
use App\Services\DeviceEventIngestionService;
use Illuminate\Http\JsonResponse;

class DeviceTelemetryController extends Controller
{
    public function __construct(private readonly DeviceEventIngestionService $deviceEventIngestionService) {}

    public function store(StoreDeviceTelemetryRequest $request): JsonResponse
    {
        /** @var Dispenser $dispenser */
        $dispenser = $request->attributes->get('dispenser');
        $result = $this->deviceEventIngestionService->ingestTelemetry(
            dispenser: $dispenser,
            payload: $request->validated(),
        );
        $sensorLog = $result['sensor_log'];
        $violations = $result['violations'];

        return response()->json([
            'message' => 'Telemetria acquisita.',
            'sensor_log_id' => $sensorLog->id,
            'threshold_exceeded' => $sensorLog->threshold_exceeded,
            'violations' => $violations->values(),
        ], 201);
    }
}
