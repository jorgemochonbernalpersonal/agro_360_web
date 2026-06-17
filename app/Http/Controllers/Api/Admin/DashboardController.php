<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Plot;
use App\Models\SecurityEvent;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    public function __invoke(Request $request): JsonResponse
    {
        $admin = $request->user();

        // Conteo de usuarios por rol
        $usersByRole = User::selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $totalUsers = User::count();

        // Usuarios pendientes de aprobación
        $pendingApprovals = User::where('can_login', false)
            ->where('role', '!=', User::ROLE_ADMIN)
            ->count();

        // Registros del último mes
        $newThisMonth = User::where('created_at', '>=', now()->startOfMonth())->count();

        // Parcelas
        $plotStats = Plot::selectRaw('COUNT(*) as total, SUM(area) as total_area')
            ->where('active', true)
            ->first();

        // Suscripciones activas
        $activeSubscriptions = Subscription::where('status', 'active')->count();

        // Últimos eventos de seguridad (resumen por nivel)
        $securitySummary = SecurityEvent::selectRaw('level, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('level')
            ->pluck('total', 'level');

        // Últimos 5 eventos de seguridad
        $recentEvents = SecurityEvent::latest('created_at')
            ->take(5)
            ->get(['id', 'level', 'event', 'message', 'ip', 'email', 'created_at']);

        return $this->success([
            'users' => [
                'total' => $totalUsers,
                'pending_approvals' => $pendingApprovals,
                'new_this_month' => $newThisMonth,
                'by_role' => [
                    'admin' => (int) ($usersByRole[User::ROLE_ADMIN] ?? 0),
                    'supervisor' => (int) ($usersByRole[User::ROLE_SUPERVISOR] ?? 0),
                    'winery' => (int) ($usersByRole[User::ROLE_WINERY] ?? 0),
                    'viticulturist' => (int) ($usersByRole[User::ROLE_VITICULTURIST] ?? 0),
                    'producer' => (int) ($usersByRole[User::ROLE_PRODUCER] ?? 0),
                ],
            ],
            'plots' => [
                'total' => (int) ($plotStats->total ?? 0),
                'total_area' => round((float) ($plotStats->total_area ?? 0), 2),
            ],
            'subscriptions' => [
                'active' => $activeSubscriptions,
            ],
            'security' => [
                // (object) garantiza un objeto JSON ({}) incluso sin eventos; sin el
                // cast, una colección vacía se serializa como [] y el cliente móvil
                // (que espera un Map) falla al deserializar el dashboard.
                'last_7_days' => (object) $securitySummary->map(fn ($v) => (int) $v)->toArray(),
                'recent' => $recentEvents->map(fn ($e) => [
                    'id' => $e->id,
                    'level' => $e->level,
                    'event' => $e->event,
                    'message' => $e->message,
                    'ip' => $e->ip,
                    'email' => $e->email,
                    'created_at' => $e->created_at->toIso8601String(),
                ])->values(),
            ],
        ]);
    }
}
