<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTherapyPlanRequest;
use App\Http\Requests\UpdateTherapyPlanRequest;
use App\Models\Medicine;
use App\Models\TherapyPlan;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TherapyPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $therapyPlans = TherapyPlan::query()
            ->with([
                'patient:id,name',
                'doctor:id,name',
                'medicine:id,name',
                'schedules:id,therapy_plan_id,scheduled_time',
            ])
            ->latest()
            ->paginate(15);

        return view('therapy-plans.index', [
            'therapyPlans' => $therapyPlans,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('therapy-plans.create', [
            'patients' => $this->selectablePatients(request()->user()),
            'medicines' => Medicine::query()->orderBy('name')->get(['id', 'name', 'patient_id']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTherapyPlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->guardPatientAccess((int) $validated['patient_id'], $request->user());
        $this->guardMedicineOwnership((int) $validated['medicine_id'], (int) $validated['patient_id']);

        /** @var TherapyPlan $therapyPlan */
        $therapyPlan = DB::transaction(function () use ($validated, $request): TherapyPlan {
            $therapyPlan = TherapyPlan::query()->create([
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $request->user()->id,
                'medicine_id' => $validated['medicine_id'],
                'dose_amount' => $validated['dose_amount'],
                'dose_unit' => $validated['dose_unit'],
                'instructions' => $validated['instructions'] ?? null,
                'starts_on' => $validated['starts_on'],
                'ends_on' => $validated['ends_on'] ?? null,
                'is_active' => (bool) $validated['is_active'],
            ]);

            $this->syncSchedules($therapyPlan, $validated['schedules']);

            return $therapyPlan;
        });

        return redirect()
            ->route('therapy-plans.show', $therapyPlan)
            ->with('status', 'Piano terapeutico creato.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TherapyPlan $therapyPlan): View
    {
        $therapyPlan->load([
            'patient:id,name',
            'doctor:id,name',
            'medicine:id,name',
            'schedules:id,therapy_plan_id,scheduled_time,timezone',
            'doseLogs' => function ($query): void {
                $query->latest('event_at')->limit(20);
            },
        ]);

        return view('therapy-plans.show', [
            'therapyPlan' => $therapyPlan,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TherapyPlan $therapyPlan): View
    {
        $scheduleValues = $therapyPlan->schedules()
            ->orderBy('scheduled_time')
            ->pluck('scheduled_time')
            ->map(static fn ($time): string => substr((string) $time, 0, 5))
            ->all();

        return view('therapy-plans.edit', [
            'therapyPlan' => $therapyPlan,
            'patients' => $this->selectablePatients(request()->user()),
            'medicines' => Medicine::query()->orderBy('name')->get(['id', 'name', 'patient_id']),
            'scheduleValues' => $scheduleValues,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTherapyPlanRequest $request, TherapyPlan $therapyPlan): RedirectResponse
    {
        $validated = $request->validated();

        $this->guardPatientAccess((int) $validated['patient_id'], $request->user());
        $this->guardMedicineOwnership((int) $validated['medicine_id'], (int) $validated['patient_id']);

        DB::transaction(function () use ($validated, $therapyPlan): void {
            $therapyPlan->update([
                'patient_id' => $validated['patient_id'],
                'medicine_id' => $validated['medicine_id'],
                'dose_amount' => $validated['dose_amount'],
                'dose_unit' => $validated['dose_unit'],
                'instructions' => $validated['instructions'] ?? null,
                'starts_on' => $validated['starts_on'],
                'ends_on' => $validated['ends_on'] ?? null,
                'is_active' => (bool) $validated['is_active'],
            ]);

            $this->syncSchedules($therapyPlan, $validated['schedules']);
        });

        return redirect()
            ->route('therapy-plans.show', $therapyPlan)
            ->with('status', 'Piano terapeutico aggiornato.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TherapyPlan $therapyPlan): RedirectResponse
    {
        $therapyPlan->delete();

        return redirect()
            ->route('therapy-plans.index')
            ->with('status', 'Piano terapeutico eliminato.');
    }

    /**
     * @param  list<int>  $patientIds
     */
    private function selectablePatients(User $user): Collection
    {
        if ($user->hasRole(UserRole::Admin)) {
            return User::query()->patients()->orderBy('name')->get(['id', 'name']);
        }

        return $user->assignedPatients()
            ->where('users.role', UserRole::Patient->value)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name']);
    }

    /**
     * @param  list<string>  $schedules
     */
    private function syncSchedules(TherapyPlan $therapyPlan, array $schedules): void
    {
        $uniqueSchedules = collect($schedules)
            ->map(static fn (string $time): string => substr($time, 0, 5))
            ->unique()
            ->sort()
            ->values();

        $therapyPlan->schedules()->delete();
        $therapyPlan->schedules()->createMany(
            $uniqueSchedules->map(static fn (string $time): array => [
                'scheduled_time' => $time,
                'timezone' => config('app.timezone'),
            ])->all(),
        );
    }

    private function guardPatientAccess(int $patientId, User $user): void
    {
        if ($user->hasRole(UserRole::Admin)) {
            return;
        }

        $allowed = $user->assignedPatients()
            ->where('users.id', $patientId)
            ->exists();

        abort_if(! $allowed, 403);
    }

    private function guardMedicineOwnership(int $medicineId, int $patientId): void
    {
        $belongsToPatient = Medicine::query()
            ->whereKey($medicineId)
            ->where('patient_id', $patientId)
            ->exists();

        if (! $belongsToPatient) {
            throw ValidationException::withMessages([
                'medicine_id' => 'Il farmaco selezionato non appartiene al paziente indicato.',
            ]);
        }
    }
}
