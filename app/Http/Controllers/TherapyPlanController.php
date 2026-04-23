<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTherapyPlanRequest;
use App\Http\Requests\UpdateTherapyPlanRequest;
use App\Models\Dispenser;
use App\Models\Medicine;
use App\Models\TherapyPlan;
use App\Models\User;
use App\Services\MqttPublisher;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(StoreTherapyPlanRequest $request, MqttPublisher $mqttPublisher): RedirectResponse
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

        // Invia la terapia al dispenser del paziente via MQTT
        $mqttSent = $this->publishTherapyToDispenser($therapyPlan, $mqttPublisher);

        return redirect()
            ->route('therapy-plans.show', $therapyPlan)
            ->with('status', 'Piano terapeutico creato.' . ($mqttSent ? ' Terapia inviata al dispenser via MQTT.' : ' Dispenser non trovato o broker MQTT non configurato.'));
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
    public function update(UpdateTherapyPlanRequest $request, TherapyPlan $therapyPlan, MqttPublisher $mqttPublisher): RedirectResponse
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

        // Ricarica le relazioni aggiornate prima dell'invio MQTT
        $therapyPlan->load(['medicine', 'schedules']);

        // Invia la terapia aggiornata al dispenser del paziente via MQTT
        $mqttSent = $this->publishTherapyToDispenser($therapyPlan, $mqttPublisher);

        return redirect()
            ->route('therapy-plans.show', $therapyPlan)
            ->with('status', 'Piano terapeutico aggiornato.' . ($mqttSent ? ' Terapia inviata al dispenser via MQTT.' : ' Dispenser non trovato o broker MQTT non configurato.'));
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
     * Invia manualmente la terapia al dispenser via MQTT.
     */
    public function sendViaMqtt(TherapyPlan $therapyPlan, MqttPublisher $mqttPublisher): RedirectResponse
    {
        $therapyPlan->load(['medicine', 'schedules']);

        $sent = $this->publishTherapyToDispenser($therapyPlan, $mqttPublisher);

        return back()->with(
            'status',
            $sent
                ? 'Terapia inviata al dispenser via MQTT.'
                : 'Dispenser non trovato o broker MQTT non configurato.'
        );
    }

    /**
     * Costruisce il payload della terapia e lo pubblica sul dispenser del paziente.
     */
    private function publishTherapyToDispenser(TherapyPlan $therapyPlan, MqttPublisher $mqttPublisher): bool
    {
        /** @var Dispenser|null $dispenser */
        $dispenser = Dispenser::query()
            ->where('patient_id', $therapyPlan->patient_id)
            ->where('is_active', true)
            ->first();

        if ($dispenser === null) {
            return false;
        }

        $therapyPlan->loadMissing(['medicine', 'schedules']);

        $schedules = $therapyPlan->schedules
            ->pluck('scheduled_time')
            ->map(static fn ($time): string => substr((string) $time, 0, 5))
            ->values()
            ->all();

        $payload = [
            'therapy_plan_id' => $therapyPlan->id,
            'medicine'        => $therapyPlan->medicine?->name,
            'dose_amount'     => (float) $therapyPlan->dose_amount,
            'dose_unit'       => $therapyPlan->dose_unit,
            'schedules'       => $schedules,
            'starts_on'       => $therapyPlan->starts_on?->toDateString(),
            'ends_on'         => $therapyPlan->ends_on?->toDateString(),
            'is_active'       => $therapyPlan->is_active,
            'instructions'    => $therapyPlan->instructions,
        ];

        return $mqttPublisher->publishCommand(
            dispenser: $dispenser,
            command: 'set_therapy',
            payload: $payload,
        );
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
