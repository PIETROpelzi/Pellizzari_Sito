<?php

use App\Http\Controllers\AlertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CareTeamController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispenserController;
use App\Http\Controllers\MedicineController;
use App\Http\Controllers\MqttCommandController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SensorLogController;
use App\Http\Controllers\TherapyPlanController;
use App\Http\Controllers\UserManagementController;
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

    Route::middleware('role:'.UserRole::Patient->value.','.UserRole::Caregiver->value)
        ->group(function (): void {
            Route::get('/care-team', [CareTeamController::class, 'index'])
                ->name('care-team.index');
            Route::post('/care-team/doctor', [CareTeamController::class, 'attachDoctor'])
                ->name('care-team.attach-doctor');
            Route::post('/care-team/caregiver', [CareTeamController::class, 'attachCaregiver'])
                ->name('care-team.attach-caregiver');
            Route::post('/care-team/patient', [CareTeamController::class, 'caregiverAttachPatient'])
                ->name('care-team.attach-patient');
        });

    Route::middleware('role:'.UserRole::Admin->value.','.UserRole::Doctor->value)
        ->group(function (): void {
            Route::get('/user-management', [UserManagementController::class, 'index'])
                ->name('user-management.index');
            Route::post('/user-management', [UserManagementController::class, 'store'])
                ->name('user-management.store');

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
