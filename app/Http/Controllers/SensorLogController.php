<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SensorLogController extends Controller
{
    public function index(Request $request): View
    {
        $patientIds = $this->resolveVisiblePatientIds($request->user());

        $sensorLogs = SensorLog::query()
            ->whereIn('patient_id', $patientIds)
            ->with(['patient:id,name', 'dispenser:id,name'])
            ->when($request->filled('patient_id'), function ($query) use ($request): void {
                $query->where('patient_id', $request->integer('patient_id'));
            })
            ->when($request->filled('from'), function ($query) use ($request): void {
                $query->where('recorded_at', '>=', $request->date('from')->startOfDay());
            })
            ->when($request->filled('to'), function ($query) use ($request): void {
                $query->where('recorded_at', '<=', $request->date('to')->endOfDay());
            })
            ->latest('recorded_at')
            ->paginate(20)
            ->withQueryString();

        return view('sensor-logs.index', [
            'sensorLogs' => $sensorLogs,
            'patients' => User::query()->whereIn('id', $patientIds)->orderBy('name')->get(['id', 'name']),
            'filters' => $request->only(['patient_id', 'from', 'to']),
        ]);
    }

    /**
     * @return list<int>
     */
    private function resolveVisiblePatientIds(User $user): array
    {
        if ($user->hasRole(UserRole::Admin)) {
            return User::query()->patients()->pluck('id')->all();
        }

        if ($user->hasRole(UserRole::Patient)) {
            return [$user->id];
        }

        return $user->assignedPatients()
            ->select('users.id')
            ->pluck('users.id')
            ->all();
    }
}
