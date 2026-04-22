<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManagedUserRequest;
use App\Models\User;
use App\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(): View
    {
        /** @var User $actor */
        $actor = request()->user();
        abort_if(! $actor->hasRole(UserRole::Admin, UserRole::Doctor), 403);

        return view('user-management.index', [
            'canCreateDoctor' => $actor->hasRole(UserRole::Admin),
            'managedUsers' => User::query()
                ->whereIn('role', [UserRole::Doctor->value, UserRole::Caregiver->value])
                ->orderBy('role')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function store(StoreManagedUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /** @var User $actor */
        $actor = $request->user();
        $role = UserRole::from($validated['role']);

        if ($role === UserRole::Doctor && ! $actor->hasRole(UserRole::Admin)) {
            abort(403);
        }

        User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return redirect()
            ->route('user-management.index')
            ->with('status', $role === UserRole::Doctor
                ? 'Dottore registrato con successo.'
                : 'Familiare registrato con successo.');
    }
}
