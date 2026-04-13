<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\PatientAssignment;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $patients = User::query()
            ->patients()
            ->with(['careTeamMembers' => function ($query): void {
                $query->select('users.id', 'users.name', 'users.role');
            }])
            ->withCount(['medicines', 'therapyPlans'])
            ->latest()
            ->paginate(15);

        return view('patients.index', [
            'patients' => $patients,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('patients.create', [
            'doctors' => User::query()->doctors()->orderBy('name')->get(['id', 'name']),
            'caregivers' => User::query()->caregivers()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $patient */
        $patient = DB::transaction(function () use ($validated, $request): User {
            $patient = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => UserRole::Patient,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'is_active' => true,
            ]);

            $this->syncAssignments(
                patient: $patient,
                doctorIds: $validated['doctor_ids'] ?? [],
                caregiverIds: $validated['caregiver_ids'] ?? [],
                assignedById: $request->user()->id,
            );

            return $patient;
        });

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Paziente creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $patient): View
    {
        abort_if(! $patient->hasRole(UserRole::Patient), 404);

        $patient->load([
            'careTeamMembers:id,name,role',
            'medicines:id,patient_id,name,remaining_quantity,reorder_threshold,updated_at',
            'therapyPlans' => function ($query): void {
                $query->with(['medicine:id,name', 'schedules:id,therapy_plan_id,scheduled_time'])
                    ->latest();
            },
            'alerts' => function ($query): void {
                $query->latest('triggered_at')->limit(10);
            },
        ]);

        return view('patients.show', [
            'patient' => $patient,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $patient): View
    {
        abort_if(! $patient->hasRole(UserRole::Patient), 404);

        $doctorIds = $patient->careTeamMembers()
            ->wherePivot('role', UserRole::Doctor->value)
            ->pluck('users.id')
            ->all();

        $caregiverIds = $patient->careTeamMembers()
            ->wherePivot('role', UserRole::Caregiver->value)
            ->pluck('users.id')
            ->all();

        return view('patients.edit', [
            'patient' => $patient,
            'doctors' => User::query()->doctors()->orderBy('name')->get(['id', 'name']),
            'caregivers' => User::query()->caregivers()->orderBy('name')->get(['id', 'name']),
            'doctorIds' => $doctorIds,
            'caregiverIds' => $caregiverIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, User $patient): RedirectResponse
    {
        abort_if(! $patient->hasRole(UserRole::Patient), 404);

        $validated = $request->validated();

        DB::transaction(function () use ($validated, $request, $patient): void {
            $patient->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'is_active' => (bool) $validated['is_active'],
            ]);

            if (! empty($validated['password'])) {
                $patient->password = Hash::make($validated['password']);
            }

            $patient->save();

            $this->syncAssignments(
                patient: $patient,
                doctorIds: $validated['doctor_ids'] ?? [],
                caregiverIds: $validated['caregiver_ids'] ?? [],
                assignedById: $request->user()->id,
            );
        });

        return redirect()
            ->route('patients.show', $patient)
            ->with('status', 'Paziente aggiornato.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $patient): RedirectResponse
    {
        abort_if(! request()->user()?->hasRole(UserRole::Admin), 403);
        abort_if(! $patient->hasRole(UserRole::Patient), 404);

        $patient->delete();

        return redirect()
            ->route('patients.index')
            ->with('status', 'Paziente eliminato.');
    }

    /**
     * @param  list<int>  $doctorIds
     * @param  list<int>  $caregiverIds
     */
    private function syncAssignments(User $patient, array $doctorIds, array $caregiverIds, int $assignedById): void
    {
        PatientAssignment::query()->where('patient_id', $patient->id)->delete();

        $assignments = collect($doctorIds)
            ->map(static fn (int $doctorId): array => [
                'patient_id' => $patient->id,
                'member_id' => $doctorId,
                'assigned_by_id' => $assignedById,
                'role' => UserRole::Doctor->value,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->merge(
                collect($caregiverIds)->map(static fn (int $caregiverId): array => [
                    'patient_id' => $patient->id,
                    'member_id' => $caregiverId,
                    'assigned_by_id' => $assignedById,
                    'role' => UserRole::Caregiver->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            )
            ->values()
            ->all();

        if ($assignments !== []) {
            PatientAssignment::query()->insert($assignments);
        }
    }
}
