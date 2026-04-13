<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\DoseLog;
use App\Models\Medicine;
use App\Models\SensorLog;
use App\Models\TherapyPlan;
use App\Models\TherapyPlanSchedule;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $patientIds = $this->resolveVisiblePatientIds($user);

        $patientsCount = User::query()->whereIn('id', $patientIds)->count();

        $activeTherapyPlans = TherapyPlan::query()
            ->whereIn('patient_id', $patientIds)
            ->where('is_active', true)
            ->whereDate('starts_on', '<=', now())
            ->where(static function ($query): void {
                $query->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', now());
            })
            ->count();

        $openAlertsCount = Alert::query()
            ->whereIn('patient_id', $patientIds)
            ->open()
            ->count();

        $lowStockCount = Medicine::query()
            ->whereIn('patient_id', $patientIds)
            ->whereColumn('remaining_quantity', '<=', 'reorder_threshold')
            ->count();

        $doseLogs = DoseLog::query()
            ->whereIn('patient_id', $patientIds)
            ->window(30)
            ->get(['status']);

        $trackedStatuses = [
            DoseLog::STATUS_TAKEN,
            DoseLog::STATUS_MISSED,
            DoseLog::STATUS_SKIPPED,
        ];

        $totalTracked = $doseLogs->whereIn('status', $trackedStatuses)->count();
        $takenCount = $doseLogs->where('status', DoseLog::STATUS_TAKEN)->count();
        $adherenceRate = $totalTracked > 0
            ? round(($takenCount / $totalTracked) * 100, 1)
            : null;

        $recentTelemetry = SensorLog::query()
            ->whereIn('patient_id', $patientIds)
            ->orderByDesc('recorded_at')
            ->limit(24)
            ->get()
            ->sortBy('recorded_at')
            ->values();

        $recentAlerts = Alert::query()
            ->whereIn('patient_id', $patientIds)
            ->with(['patient:id,name', 'dispenser:id,name'])
            ->latest('triggered_at')
            ->limit(8)
            ->get();

        $upcomingDoses = $this->buildUpcomingDosePreview($patientIds);

        return view('dashboard.index', [
            'patientsCount' => $patientsCount,
            'activeTherapyPlans' => $activeTherapyPlans,
            'openAlertsCount' => $openAlertsCount,
            'lowStockCount' => $lowStockCount,
            'adherenceRate' => $adherenceRate,
            'recentTelemetry' => $recentTelemetry,
            'recentAlerts' => $recentAlerts,
            'upcomingDoses' => $upcomingDoses,
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

        $assignedPatients = $user->assignedPatients()
            ->select('users.id')
            ->pluck('users.id')
            ->all();

        if ($assignedPatients !== []) {
            return $assignedPatients;
        }

        return [];
    }

    /**
     * @param  list<int>  $patientIds
     * @return Collection<int, array<string, mixed>>
     */
    private function buildUpcomingDosePreview(array $patientIds): Collection
    {
        if ($patientIds === []) {
            return collect();
        }

        $plans = TherapyPlan::query()
            ->whereIn('patient_id', $patientIds)
            ->where('is_active', true)
            ->whereDate('starts_on', '<=', now())
            ->where(static function ($query): void {
                $query->whereNull('ends_on')
                    ->orWhereDate('ends_on', '>=', now());
            })
            ->with(['patient:id,name', 'medicine:id,name', 'schedules:id,therapy_plan_id,scheduled_time,week_days,timezone'])
            ->get();

        $now = now();
        $result = collect();

        foreach ($plans as $plan) {
            foreach ($plan->schedules as $schedule) {
                $time = (string) $schedule->getRawOriginal('scheduled_time');
                $timezone = $schedule->timezone ?: config('app.timezone');
                $date = now($timezone);
                $candidate = $date->copy()->setTimeFromTimeString($time);
                $weekDays = collect($schedule->week_days ?? [])->map(static fn ($day): int => (int) $day)->all();

                if ($candidate->lessThan($now->copy()->setTimezone($timezone))) {
                    $candidate->addDay();
                }

                if ($weekDays !== []) {
                    while (! in_array($candidate->dayOfWeekIso, $weekDays, true)) {
                        $candidate->addDay();
                    }
                }

                $result->push([
                    'patient_name' => $plan->patient->name,
                    'medicine_name' => $plan->medicine->name,
                    'dose' => $plan->dose_amount.' '.$plan->dose_unit,
                    'scheduled_at' => $candidate->setTimezone(config('app.timezone'))->toDateTimeString(),
                ]);
            }
        }

        return $result->sortBy('scheduled_at')->take(10)->values();
    }
}
