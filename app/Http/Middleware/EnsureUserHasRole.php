<?php

namespace App\Http\Middleware;

use App\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $allowedRoles = array_map(
            static fn (string $role): string => UserRole::tryFrom($role)?->value ?? $role,
            $roles,
        );

        if (! $user->hasRole(...$allowedRoles)) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
