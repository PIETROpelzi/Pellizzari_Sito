<?php

namespace App\Http\Middleware;

use App\Models\Dispenser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateDevice
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = (string) ($request->header('X-Device-Token') ?? $request->bearerToken() ?? '');

        if ($token === '') {
            return response()->json([
                'message' => 'Device token mancante.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $dispenser = Dispenser::query()
            ->with('patient')
            ->where('api_token', $token)
            ->where('is_active', true)
            ->first();

        if ($dispenser === null) {
            return response()->json([
                'message' => 'Dispositivo non autorizzato.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $request->attributes->set('dispenser', $dispenser);

        return $next($request);
    }
}
