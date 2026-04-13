<?php

use App\Http\Controllers\Api\DeviceDoseLogController;
use App\Http\Controllers\Api\DevicePlanController;
use App\Http\Controllers\Api\DeviceTelemetryController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1/device')
    ->middleware('device.auth')
    ->group(function (): void {
        Route::get('/plans', [DevicePlanController::class, 'index'])
            ->name('api.device.plans');
        Route::post('/telemetry', [DeviceTelemetryController::class, 'store'])
            ->name('api.device.telemetry');
        Route::post('/dose-logs', [DeviceDoseLogController::class, 'store'])
            ->name('api.device.dose-logs');
    });

