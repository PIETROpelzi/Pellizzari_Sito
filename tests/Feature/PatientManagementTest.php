<?php

use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;

test('doctor can register a patient with registered family members', function () {
    $doctor = User::factory()->doctor()->create();
    $caregiverOne = User::factory()->caregiver()->create();
    $caregiverTwo = User::factory()->caregiver()->create();

    $response = $this->actingAs($doctor)->post(route('patients.store'), [
        'name' => 'Mario Test',
        'email' => 'mario.test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'address' => 'Via Roma 1',
        'date_of_birth' => '1970-05-10',
        'caregiver_ids' => [$caregiverOne->id, $caregiverTwo->id],
    ]);

    $patient = User::query()->where('email', 'mario.test@example.com')->first();

    expect($patient)->not->toBeNull();
    expect($patient->role)->toBe(UserRole::Patient);

    $response->assertRedirect(route('patients.show', $patient));

    expect(
        PatientAssignment::query()
            ->where('patient_id', $patient->id)
            ->where('member_id', $doctor->id)
            ->where('role', UserRole::Doctor->value)
            ->exists()
    )->toBeTrue();

    expect(
        PatientAssignment::query()
            ->where('patient_id', $patient->id)
            ->whereIn('member_id', [$caregiverOne->id, $caregiverTwo->id])
            ->where('role', UserRole::Caregiver->value)
            ->count()
    )->toBe(2);
});

test('admin can select the assigned doctor during patient registration', function () {
    $admin = User::factory()->admin()->create();
    $selectedDoctor = User::factory()->doctor()->create();
    $caregiver = User::factory()->caregiver()->create();

    $response = $this->actingAs($admin)->post(route('patients.store'), [
        'name' => 'Luca Admin Flow',
        'email' => 'luca.admin.flow@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'doctor_ids' => [$selectedDoctor->id],
        'caregiver_ids' => [$caregiver->id],
    ]);

    $patient = User::query()->where('email', 'luca.admin.flow@example.com')->first();

    expect($patient)->not->toBeNull();
    $response->assertRedirect(route('patients.show', $patient));

    expect(
        PatientAssignment::query()
            ->where('patient_id', $patient->id)
            ->where('member_id', $selectedDoctor->id)
            ->where('role', UserRole::Doctor->value)
            ->exists()
    )->toBeTrue();
});

test('admin must choose at least one doctor when registering a patient', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->from(route('patients.create'))
        ->post(route('patients.store'), [
            'name' => 'Paziente Senza Dottore',
            'email' => 'senza.dottore@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect(route('patients.create'));
    $response->assertSessionHasErrors(['doctor_ids']);
});
