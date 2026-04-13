<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicineRequest;
use App\Http\Requests\UpdateMedicineRequest;
use App\Models\Medicine;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $medicines = Medicine::query()
            ->with(['patient:id,name'])
            ->withCount('therapyPlans')
            ->latest()
            ->paginate(15);

        return view('medicines.index', [
            'medicines' => $medicines,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('medicines.create', [
            'patients' => $this->selectablePatients(request()->user()),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicineRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->guardPatientAccess((int) $data['patient_id'], $request->user());

        $medicine = Medicine::query()->create([
            ...$data,
            'created_by_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('status', 'Farmaco creato con successo.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Medicine $medicine): View
    {
        $medicine->load([
            'patient:id,name',
            'therapyPlans' => function ($query): void {
                $query->with('schedules:id,therapy_plan_id,scheduled_time')
                    ->where('is_active', true);
            },
        ]);

        $dailyDose = $medicine->therapyPlans->sum(
            static fn ($plan): float => (float) $plan->dose_amount * max($plan->schedules->count(), 1),
        );

        $estimatedDepletionDate = null;
        if ($dailyDose > 0 && $medicine->remaining_quantity > 0) {
            $estimatedDepletionDate = now()->addDays((int) floor($medicine->remaining_quantity / $dailyDose));
        }

        return view('medicines.show', [
            'medicine' => $medicine,
            'dailyDose' => $dailyDose,
            'estimatedDepletionDate' => $estimatedDepletionDate,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medicine $medicine): View
    {
        return view('medicines.edit', [
            'medicine' => $medicine,
            'patients' => $this->selectablePatients(request()->user()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicineRequest $request, Medicine $medicine): RedirectResponse
    {
        $data = $request->validated();

        $this->guardPatientAccess((int) $data['patient_id'], $request->user());

        $medicine->update($data);

        return redirect()
            ->route('medicines.show', $medicine)
            ->with('status', 'Farmaco aggiornato.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medicine $medicine): RedirectResponse
    {
        $medicine->delete();

        return redirect()
            ->route('medicines.index')
            ->with('status', 'Farmaco eliminato.');
    }

    /**
     * @return Collection<int, User>
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
}
