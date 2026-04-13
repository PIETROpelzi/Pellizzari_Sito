<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlertController extends Controller
{
    public function index(Request $request): View
    {
        $patientIds = $this->resolveVisiblePatientIds($request->user());

        $alerts = Alert::query()
            ->whereIn('patient_id', $patientIds)
            ->with(['patient:id,name', 'dispenser:id,name'])
            ->when($request->filled('status'), function ($query) use ($request): void {
                if ($request->string('status')->toString() === 'open') {
                    $query->open();
                }

                if ($request->string('status')->toString() === 'resolved') {
                    $query->resolved();
                }
            })
            ->latest('triggered_at')
            ->paginate(20)
            ->withQueryString();

        return view('alerts.index', [
            'alerts' => $alerts,
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function resolve(Alert $alert): RedirectResponse
    {
        $alert->update([
            'resolved_at' => now(),
        ]);

        return back()->with('status', 'Alert risolto.');
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
