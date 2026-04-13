<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispenserController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MqttCommandController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SensorLogController;
use App\Http\Controllers\TherapyPlanController;
use App\UserRole;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/sensor-logs', [SensorLogController::class, 'index'])
        ->name('sensor-logs.index');

    Route::get('/alerts', [AlertController::class, 'index'])
        ->name('alerts.index');

    Route::middleware('role:'.UserRole::Admin->value.','.UserRole::Doctor->value)
        ->group(function (): void {
            Route::resource('patients', PatientController::class)
                ->parameters(['patients' => 'patient']);
            Route::resource('medicines', MedicineController::class);
            Route::resource('therapy-plans', TherapyPlanController::class);
            Route::resource('dispensers', DispenserController::class);

            Route::patch('/alerts/{alert}/resolve', [AlertController::class, 'resolve'])
                ->name('alerts.resolve');

            Route::post('/dispensers/{dispenser}/mqtt-command', MqttCommandController::class)
                ->name('dispensers.mqtt-command');
        });
});
