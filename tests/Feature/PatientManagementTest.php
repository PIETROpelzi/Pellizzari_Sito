<?php

use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;

test('doctor can create a patient and assign care team', function () {
    $doctor = User::factory()->doctor()->create();
    $caregiver = User::factory()->caregiver()->create();

    $response = $this->actingAs($doctor)->post(route('patients.store'), [
        'name' => 'Mario Test',
        'email' => 'mario.test@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'phone' => '123456789',
        'address' => 'Via Roma 1',
        'date_of_birth' => '1970-05-10',
        'doctor_ids' => [$doctor->id],
        'caregiver_ids' => [$caregiver->id],
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
});
