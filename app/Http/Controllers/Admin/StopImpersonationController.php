<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StopImpersonationController extends Controller
{
    public function __invoke(Request $request)
    {
        $adminId = session()->get('admin_id');
        $targetUserId = Auth::id();

        // Solo funciona si hay una sesión de impersonación activa
        if (!$adminId || !session()->has('impersonating')) {
            abort(403);
        }

        $admin = User::find($adminId);

        if (!$admin || !$admin->isAdmin()) {
            session()->forget(['impersonating', 'admin_id', 'admin_name', 'impersonation_started_at']);
            Auth::logout();
            return redirect()->route('login');
        }

        // Log de seguridad
        SecurityLogger::logImpersonationEnded($adminId, $targetUserId);

        // Restaurar sesión del admin
        Auth::login($admin);
        session()->regenerate();

        // Limpiar datos de impersonación
        session()->forget(['impersonating', 'admin_id', 'admin_name', 'impersonation_started_at']);

        return redirect()->route('admin.users.index')
            ->with('success', 'Has vuelto a tu sesión de administrador.');
    }
}
