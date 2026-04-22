<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttachCaregiverToPatientRequest;
use App\Http\Requests\AttachDoctorToPatientRequest;
use App\Http\Requests\CaregiverAttachPatientRequest;
use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CareTeamController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->hasRole(UserRole::Patient)) {
            return view('care-team.index', [
                'mode' => 'patient',
                'availableDoctors' => User::query()
                    ->doctors()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email']),
                'availableCaregivers' => User::query()
                    ->caregivers()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email']),
                'linkedDoctors' => $user->careTeamMembers()
                    ->wherePivot('role', UserRole::Doctor->value)
                    ->orderBy('users.name')
                    ->get(['users.id', 'users.name', 'users.email']),
                'linkedCaregivers' => $user->careTeamMembers()
                    ->wherePivot('role', UserRole::Caregiver->value)
                    ->orderBy('users.name')
                    ->get(['users.id', 'users.name', 'users.email']),
                'availablePatients' => collect(),
                'linkedPatients' => collect(),
            ]);
        }

        if ($user->hasRole(UserRole::Caregiver)) {
            return view('care-team.index', [
                'mode' => 'caregiver',
                'availableDoctors' => collect(),
                'availableCaregivers' => collect(),
                'linkedDoctors' => collect(),
                'linkedCaregivers' => collect(),
                'availablePatients' => User::query()
                    ->patients()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name', 'email']),
                'linkedPatients' => $user->assignedPatients()
                    ->wherePivot('role', UserRole::Caregiver->value)
                    ->orderBy('users.name')
                    ->get(['users.id', 'users.name', 'users.email']),
            ]);
        }

        abort(403);
    }

    public function attachDoctor(AttachDoctorToPatientRequest $request): RedirectResponse
    {
        /** @var User $patient */
        $patient = $request->user();
        $validated = $request->validated();

        $assignment = PatientAssignment::query()->firstOrCreate(
            [
                'patient_id' => $patient->id,
                'member_id' => (int) $validated['doctor_id'],
                'role' => UserRole::Doctor->value,
            ],
            [
                'assigned_by_id' => $patient->id,
            ],
        );

        return back()->with('status', $assignment->wasRecentlyCreated
            ? 'Dottore collegato al paziente.'
            : 'Il dottore selezionato e gia collegato.');
    }

    public function attachCaregiver(AttachCaregiverToPatientRequest $request): RedirectResponse
    {
        /** @var User $patient */
        $patient = $request->user();
        $validated = $request->validated();

        $assignment = PatientAssignment::query()->firstOrCreate(
            [
                'patient_id' => $patient->id,
                'member_id' => (int) $validated['caregiver_id'],
                'role' => UserRole::Caregiver->value,
            ],
            [
                'assigned_by_id' => $patient->id,
            ],
        );

        return back()->with('status', $assignment->wasRecentlyCreated
            ? 'Familiare collegato al paziente.'
            : 'Il familiare selezionato e gia collegato.');
    }

    public function caregiverAttachPatient(CaregiverAttachPatientRequest $request): RedirectResponse
    {
        /** @var User $caregiver */
        $caregiver = $request->user();
        $validated = $request->validated();

        $assignment = PatientAssignment::query()->firstOrCreate(
            [
                'patient_id' => (int) $validated['patient_id'],
                'member_id' => $caregiver->id,
                'role' => UserRole::Caregiver->value,
            ],
            [
                'assigned_by_id' => $caregiver->id,
            ],
        );

        return back()->with('status', $assignment->wasRecentlyCreated
            ? 'Paziente collegato al tuo profilo familiare.'
            : 'Sei gia collegato a questo paziente.');
    }
}
