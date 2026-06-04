<?php

namespace App\Http\Middleware;

use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        $roleMatch = empty($roles) || in_array($user->role, $roles);
        $tokenMatch = empty($roles) || collect($roles)->contains(fn ($r) => $user->tokenCan($r));

        if ($roleMatch && $tokenMatch) {
            return $next($request);
        }

        SecurityLogger::logAccessDenied(
            $user->id,
            $request->path(),
            'api_role_mismatch: required='.implode(',', $roles).' actual='.$user->role
        );

        return response()->json(['message' => __('No tienes permiso para acceder a este recurso.')], 403);
    }
}
