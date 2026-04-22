<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDeviceDoseLogRequest;
use App\Models\Dispenser;
use App\Services\DeviceEventIngestionService;
use Illuminate\Http\JsonResponse;

class DeviceDoseLogController extends Controller
{
    public function __construct(private readonly DeviceEventIngestionService $deviceEventIngestionService) {}

    public function store(StoreDeviceDoseLogRequest $request): JsonResponse
    {
        /** @var Dispenser $dispenser */
        $dispenser = $request->attributes->get('dispenser');

        $doseLog = $this->deviceEventIngestionService->ingestDoseLog(
            dispenser: $dispenser,
            payload: $request->validated(),
        );

        return response()->json([
            'message' => 'Evento dose registrato.',
            'dose_log_id' => $doseLog->id,
        ], 201);
    }
}
