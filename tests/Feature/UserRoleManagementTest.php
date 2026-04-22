<?php

use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;

test('admin can register a doctor from user management', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)
        ->from(route('user-management.index'))
        ->post(route('user-management.store'), [
            'role' => UserRole::Doctor->value,
            'name' => 'Dr. Test Admin',
            'email' => 'doctor.from.admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_active' => '1',
        ]);

    $response->assertRedirect(route('user-management.index'));

    $doctor = User::query()->where('email', 'doctor.from.admin@example.com')->first();
    expect($doctor)->not->toBeNull();
    expect($doctor->role)->toBe(UserRole::Doctor);
});

test('doctor can register caregiver but cannot register another doctor', function () {
    $doctor = User::factory()->doctor()->create();

    $caregiverResponse = $this->actingAs($doctor)
        ->from(route('user-management.index'))
        ->post(route('user-management.store'), [
            'role' => UserRole::Caregiver->value,
            'name' => 'Familiare Test',
            'email' => 'caregiver.from.doctor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $caregiverResponse->assertRedirect(route('user-management.index'));

    $createdCaregiver = User::query()->where('email', 'caregiver.from.doctor@example.com')->first();
    expect($createdCaregiver)->not->toBeNull();
    expect($createdCaregiver->role)->toBe(UserRole::Caregiver);

    $doctorResponse = $this->actingAs($doctor)
        ->from(route('user-management.index'))
        ->post(route('user-management.store'), [
            'role' => UserRole::Doctor->value,
            'name' => 'Doctor Non Consentito',
            'email' => 'doctor.not.allowed@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $doctorResponse->assertRedirect(route('user-management.index'));
    $doctorResponse->assertSessionHasErrors(['role']);

    expect(
        User::query()->where('email', 'doctor.not.allowed@example.com')->exists()
    )->toBeFalse();
});

test('patient can attach a registered doctor and caregiver to self', function () {
    $patient = User::factory()->patient()->create();
    $doctor = User::factory()->doctor()->create();
    $caregiver = User::factory()->caregiver()->create();

    $this->actingAs($patient)
        ->from(route('care-team.index'))
        ->post(route('care-team.attach-doctor'), [
            'doctor_id' => $doctor->id,
        ])->assertRedirect(route('care-team.index'));

    $this->actingAs($patient)
        ->from(route('care-team.index'))
        ->post(route('care-team.attach-caregiver'), [
            'caregiver_id' => $caregiver->id,
        ])->assertRedirect(route('care-team.index'));

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
            ->where('member_id', $caregiver->id)
            ->where('role', UserRole::Caregiver->value)
            ->exists()
    )->toBeTrue();
});

test('caregiver can attach self to a registered patient', function () {
    $caregiver = User::factory()->caregiver()->create();
    $patient = User::factory()->patient()->create();

    $response = $this->actingAs($caregiver)
        ->from(route('care-team.index'))
        ->post(route('care-team.attach-patient'), [
            'patient_id' => $patient->id,
        ]);

    $response->assertRedirect(route('care-team.index'));

    expect(
        PatientAssignment::query()
            ->where('patient_id', $patient->id)
            ->where('member_id', $caregiver->id)
            ->where('role', UserRole::Caregiver->value)
            ->exists()
    )->toBeTrue();
});
