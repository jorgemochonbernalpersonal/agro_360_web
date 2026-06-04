<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\SecurityLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonationTimeout
{
    private const TIMEOUT_MINUTES = 60;

    public function handle(Request $request, Closure $next): Response
    {
        if (! session()->has('impersonating')) {
            return $next($request);
        }

        $startedAt = session()->get('impersonation_started_at');

        // If no timestamp (legacy sessions), set it now
        if (! $startedAt) {
            session()->put('impersonation_started_at', now()->timestamp);

            return $next($request);
        }

        $elapsed = now()->timestamp - $startedAt;

        if ($elapsed > self::TIMEOUT_MINUTES * 60) {
            $adminId = session()->get('admin_id');
            $targetUserId = Auth::id();

            SecurityLogger::logSecurityEvent('impersonation_timeout', [
                'admin_id' => $adminId,
                'target_user_id' => $targetUserId,
                'duration_min' => round($elapsed / 60),
            ]);

            // Restore admin session
            $admin = User::find($adminId);
            if ($admin) {
                Auth::login($admin);
                session()->regenerate();
            }

            session()->forget(['impersonating', 'admin_id', 'admin_name', 'impersonation_started_at']);

            return redirect()->route('admin.users.index')
                ->with('warning', __('La sesión de impersonación ha expirado tras :minutes minutos.', ['minutes' => self::TIMEOUT_MINUTES]));
        }

        return $next($request);
    }
}
