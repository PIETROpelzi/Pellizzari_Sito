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
        $canSelectDoctors = request()->user()?->hasRole(UserRole::Admin) ?? false;

        return view('patients.create', [
            'doctors' => $canSelectDoctors
                ? User::query()->doctors()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'caregivers' => User::query()->caregivers()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'canSelectDoctors' => $canSelectDoctors,
            'caregiverIds' => [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $doctorIds = $this->resolveDoctorIdsForStore($validated, $request->user());
        $caregiverIds = $this->resolveCaregiverIds($validated);

        /** @var User $patient */
        $patient = DB::transaction(function () use ($validated, $request, $doctorIds, $caregiverIds): User {
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
                doctorIds: $doctorIds,
                caregiverIds: $caregiverIds,
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

        $canSelectDoctors = request()->user()?->hasRole(UserRole::Admin) ?? false;

        return view('patients.edit', [
            'patient' => $patient,
            'doctors' => $canSelectDoctors
                ? User::query()->doctors()->orderBy('name')->get(['id', 'name'])
                : collect(),
            'caregivers' => User::query()->caregivers()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'email']),
            'canSelectDoctors' => $canSelectDoctors,
            'caregiverIds' => $caregiverIds,
            'doctorIds' => $doctorIds,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, User $patient): RedirectResponse
    {
        abort_if(! $patient->hasRole(UserRole::Patient), 404);

        $validated = $request->validated();
        $caregiverIds = $this->resolveCaregiverIds($validated);

        DB::transaction(function () use ($validated, $request, $patient, $caregiverIds): void {
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

            $doctorIds = $this->resolveDoctorIdsForUpdate(
                validated: $validated,
                actor: $request->user(),
                patient: $patient,
            );

            $this->syncAssignments(
                patient: $patient,
                doctorIds: $doctorIds,
                caregiverIds: $caregiverIds,
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

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    private function resolveDoctorIdsForStore(array $validated, User $actor): array
    {
        if ($actor->hasRole(UserRole::Admin)) {
            return collect($validated['doctor_ids'] ?? [])
                ->map(static fn (mixed $doctorId): int => (int) $doctorId)
                ->unique()
                ->values()
                ->all();
        }

        return [$actor->id];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    private function resolveDoctorIdsForUpdate(array $validated, User $actor, User $patient): array
    {
        if ($actor->hasRole(UserRole::Admin)) {
            return collect($validated['doctor_ids'] ?? [])
                ->map(static fn (mixed $doctorId): int => (int) $doctorId)
                ->unique()
                ->values()
                ->all();
        }

        $currentDoctorIds = $patient->careTeamMembers()
            ->wherePivot('role', UserRole::Doctor->value)
            ->pluck('users.id')
            ->map(static fn (int|string $doctorId): int => (int) $doctorId)
            ->all();

        return collect($currentDoctorIds)
            ->push($actor->id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return list<int>
     */
    private function resolveCaregiverIds(array $validated): array
    {
        return collect($validated['caregiver_ids'] ?? [])
            ->map(static fn (mixed $caregiverId): int => (int) $caregiverId)
            ->unique()
            ->values()
            ->all();
    }
}
