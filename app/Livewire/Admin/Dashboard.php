<?php

namespace App\Livewire\Admin;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function exportCsv()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        $filename = 'usuarios_'.now()->format('Y-m-d_H-i-s').'.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($handle, ['ID', 'Nombre', 'Email', 'Rol', 'Estado', 'Email Verificado', 'Beta', 'Fin Beta', 'Última Conexión', 'Registro']);

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role,
                    $user->can_login ? 'Activo' : 'Inactivo',
                    $user->email_verified_at ? 'Sí' : 'No',
                    $user->is_beta_user ? 'Sí' : 'No',
                    $user->beta_ends_at ? $user->beta_ends_at->format('d/m/Y') : '',
                    $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Nunca',
                    $user->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function refreshStats(): void
    {
        Cache::forget($this->cacheKey());
    }

    public function render()
    {
        $cached = Cache::remember($this->cacheKey(), now()->addMinutes(5), function () {
            return [
                'stats' => $this->buildStats(),
                'cached_at' => now()->timestamp,
            ];
        });

        return view('livewire.admin.dashboard', [
            'stats' => $cached['stats'],
            'cachedAt' => $cached['cached_at'],
            'suspiciousUsers' => $this->buildSuspiciousUsers(),
            'orphanedPlots' => $this->orphanedPlotsCount(),
            'registrationsChart' => $this->buildRegistrationsChart(),
            'activationFunnel' => $this->buildActivationFunnel(),
            'activityHeatmap' => $this->buildActivityHeatmap(),
            'geoDistribution' => $this->buildGeoDistribution(),
        ])->layout('layouts.app', [
            'title' => __('Dashboard Administrador - Agro365'),
            'description' => __('Panel de control con estadísticas generales del sistema'),
        ]);
    }

    private function cacheKey(): string
    {
        return 'admin.dashboard.stats.'.now()->format('Y.m');
    }

    private function buildStats(): array
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        $userQuery = DB::table('users')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin")
            ->selectRaw("SUM(CASE WHEN role = 'supervisor' THEN 1 ELSE 0 END) as supervisor")
            ->selectRaw("SUM(CASE WHEN role = 'winery' THEN 1 ELSE 0 END) as winery")
            ->selectRaw("SUM(CASE WHEN role = 'viticulturist' THEN 1 ELSE 0 END) as viticulturist")
            ->selectRaw("SUM(CASE WHEN role = 'producer' THEN 1 ELSE 0 END) as producer")
            ->selectRaw('SUM(CASE WHEN can_login = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw('SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified')
            ->selectRaw('SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month', [$month, $year]);
        $userStats = $userQuery->first();

        $plotQuery = DB::table('plots')
            ->join('users', 'users.id', '=', 'plots.viticulturist_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COALESCE(SUM(plots.area), 0) as total_area')
            ->selectRaw('SUM(CASE WHEN MONTH(plots.created_at) = ? AND YEAR(plots.created_at) = ? THEN 1 ELSE 0 END) as new_this_month', [$month, $year]);
        $plotStats = $plotQuery->first();

        $clientQuery = DB::table('clients')
            ->join('users', 'users.id', '=', 'clients.user_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN clients.active = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw("SUM(CASE WHEN clients.client_type = 'individual' THEN 1 ELSE 0 END) as individual")
            ->selectRaw("SUM(CASE WHEN clients.client_type = 'company' THEN 1 ELSE 0 END) as company");
        $clientStats = $clientQuery->first();

        $invoiceQuery = DB::table('invoices')
            ->join('users', 'users.id', '=', 'invoices.user_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN YEAR(invoices.invoice_date) = ? THEN 1 ELSE 0 END) as this_year', [$year])
            ->selectRaw('COALESCE(SUM(CASE WHEN YEAR(invoices.invoice_date) = ? THEN invoices.total_amount ELSE 0 END), 0) as this_year_amount', [$year])
            ->selectRaw("SUM(CASE WHEN invoices.payment_status = 'unpaid' AND invoices.status != 'cancelled' THEN 1 ELSE 0 END) as pending");
        $invoiceStats = $invoiceQuery->first();

        $activityQuery = DB::table('agricultural_activities')
            ->join('users', 'users.id', '=', 'agricultural_activities.viticulturist_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN YEAR(agricultural_activities.activity_date) = ? THEN 1 ELSE 0 END) as this_year', [$year])
            ->selectRaw('SUM(CASE WHEN YEAR(agricultural_activities.activity_date) = ? AND MONTH(agricultural_activities.activity_date) = ? THEN 1 ELSE 0 END) as this_month', [$year, $month]);
        $activityStats = $activityQuery->first();

        $weekAgo = now()->subWeek()->toDateTimeString();
        $supportStats = DB::table('support_tickets')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('open', 'in_progress') THEN 1 ELSE 0 END) as open")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved")
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_week', [$weekAgo])
            ->first();

        // ── SaaS metrics ──────────────────────────────────────────────────────
        $subBase = DB::table('subscriptions')
            ->join('users', 'users.id', '=', 'subscriptions.user_id');

        // MRR: active subs normalized to monthly
        $mrrMonthly = (clone $subBase)
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.ends_at', '>', now())
            ->where('subscriptions.plan_type', 'monthly')
            ->sum('subscriptions.amount');

        $mrrYearly = (clone $subBase)
            ->where('subscriptions.status', 'active')
            ->where('subscriptions.ends_at', '>', now())
            ->where('subscriptions.plan_type', 'yearly')
            ->sum('subscriptions.amount');

        $mrr = round((float) $mrrMonthly + ((float) $mrrYearly / 12), 2);

        $saasStats = (clone $subBase)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN subscriptions.status = 'active' AND subscriptions.ends_at > NOW() THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN subscriptions.plan_type = 'monthly' AND subscriptions.status = 'active' AND subscriptions.ends_at > NOW() THEN 1 ELSE 0 END) as active_monthly")
            ->selectRaw("SUM(CASE WHEN subscriptions.plan_type = 'yearly'  AND subscriptions.status = 'active' AND subscriptions.ends_at > NOW() THEN 1 ELSE 0 END) as active_yearly")
            ->selectRaw('SUM(CASE WHEN MONTH(subscriptions.starts_at) = ? AND YEAR(subscriptions.starts_at) = ? THEN 1 ELSE 0 END) as new_this_month', [$month, $year])
            ->selectRaw("SUM(CASE WHEN subscriptions.status = 'cancelled' AND MONTH(subscriptions.cancelled_at) = ? AND YEAR(subscriptions.cancelled_at) = ? THEN 1 ELSE 0 END) as cancelled_this_month", [$month, $year])
            ->selectRaw("SUM(CASE WHEN subscriptions.status IN ('cancelled','expired') THEN 1 ELSE 0 END) as churned_total")
            ->first();

        $newThisMonth = (int) ($saasStats->new_this_month ?? 0);
        $cancelledThisMonth = (int) ($saasStats->cancelled_this_month ?? 0);
        $activeCount = (int) ($saasStats->active ?? 0);

        // Churn rate = cancelled this month / (active + cancelled this month) * 100
        $churnDenominator = $activeCount + $cancelledThisMonth;
        $churnRate = $churnDenominator > 0
            ? round($cancelledThisMonth / $churnDenominator * 100, 1)
            : 0.0;

        return [
            'users' => [
                'total' => (int) $userStats->total,
                'by_role' => [
                    'admin' => (int) $userStats->admin,
                    'supervisor' => (int) $userStats->supervisor,
                    'winery' => (int) $userStats->winery,
                    'viticulturist' => (int) $userStats->viticulturist,
                    'producer' => (int) $userStats->producer,
                ],
                'active' => (int) $userStats->active,
                'verified' => (int) $userStats->verified,
                'new_this_month' => (int) $userStats->new_this_month,
            ],
            'plots' => [
                'total' => (int) $plotStats->total,
                'total_area' => (float) $plotStats->total_area,
                'new_this_month' => (int) $plotStats->new_this_month,
            ],
            'clients' => [
                'total' => (int) $clientStats->total,
                'active' => (int) $clientStats->active,
                'individual' => (int) $clientStats->individual,
                'company' => (int) $clientStats->company,
            ],
            'invoices' => [
                'total' => (int) $invoiceStats->total,
                'this_year' => (int) $invoiceStats->this_year,
                'this_year_amount' => (float) $invoiceStats->this_year_amount,
                'pending' => (int) $invoiceStats->pending,
            ],
            'activities' => [
                'total' => (int) $activityStats->total,
                'this_year' => (int) $activityStats->this_year,
                'this_month' => (int) $activityStats->this_month,
            ],
            'support' => [
                'total' => (int) $supportStats->total,
                'open' => (int) $supportStats->open,
                'in_progress' => (int) $supportStats->in_progress,
                'resolved' => (int) $supportStats->resolved,
                'new_this_week' => (int) $supportStats->new_this_week,
            ],
            'saas' => [
                'mrr' => $mrr,
                'arr' => round($mrr * 12, 2),
                'active' => $activeCount,
                'active_monthly' => (int) ($saasStats->active_monthly ?? 0),
                'active_yearly' => (int) ($saasStats->active_yearly ?? 0),
                'new_this_month' => $newThisMonth,
                'cancelled_this_month' => $cancelledThisMonth,
                'net_new' => $newThisMonth - $cancelledThisMonth,
                'churn_rate' => $churnRate,
            ],
        ];
    }

    private function buildActivityHeatmap(): array
    {
        try {
            $query = DB::table('agricultural_activities')
                ->join('users', 'users.id', '=', 'agricultural_activities.viticulturist_id')
                ->selectRaw('DATE(activity_date) as date, COUNT(*) as count')
                ->where('activity_date', '>=', now()->subWeeks(51)->startOfWeek());

            $raw = $query->groupBy('date')->get()->keyBy('date');

            // Build 52 weeks × 7 days grid
            $start = now()->startOfWeek()->subWeeks(51);
            $grid = [];
            $max = 0;

            for ($w = 0; $w < 52; $w++) {
                $week = [];
                for ($d = 0; $d < 7; $d++) {
                    $date = $start->copy()->addWeeks($w)->addDays($d)->format('Y-m-d');
                    $count = (int) ($raw[$date]->count ?? 0);
                    if ($count > $max) {
                        $max = $count;
                    }
                    $week[] = ['date' => $date, 'count' => $count];
                }
                $grid[] = $week;
            }

            return ['grid' => $grid, 'max' => max($max, 1)];
        } catch (\Throwable) {
            return ['grid' => [], 'max' => 1];
        }
    }

    private function buildGeoDistribution(): array
    {
        try {
            return DB::table('plots')
                ->join('users', 'users.id', '=', 'plots.viticulturist_id')
                ->join('municipalities', 'municipalities.id', '=', 'plots.municipality_id')
                ->join('provinces', 'provinces.id', '=', 'municipalities.province_id')
                ->select('provinces.name', DB::raw('COUNT(plots.id) as plots'), DB::raw('COALESCE(SUM(plots.area),0) as area'))
                ->groupBy('provinces.name')
                ->orderByDesc('plots')
                ->limit(10)
                ->get()
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildRegistrationsChart(): array
    {
        try {
            $query = DB::table('users')
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', now()->subDays(13)->startOfDay());

            $raw = $query->groupBy('date')->get()->keyBy('date');

            $chart = [];
            for ($i = 13; $i >= 0; $i--) {
                $date = now()->subDays($i)->format('Y-m-d');
                $chart[] = [
                    'label' => now()->subDays($i)->format('d/m'),
                    'count' => (int) ($raw[$date]->count ?? 0),
                ];
            }

            return $chart;
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildActivationFunnel(): array
    {
        try {
            $base = DB::table('users');

            $total = (clone $base)->count();
            $verified = (clone $base)->whereNotNull('email_verified_at')->count();
            $withPlots = (clone $base)
                ->join('plots', 'plots.viticulturist_id', '=', 'users.id')
                ->distinct('users.id')
                ->count('users.id');
            $withActivities = (clone $base)
                ->join('agricultural_activities', 'agricultural_activities.viticulturist_id', '=', 'users.id')
                ->distinct('users.id')
                ->count('users.id');
            $active30d = (clone $base)
                ->where('last_login_at', '>=', now()->subDays(30))
                ->count();

            return [
                ['label' => __('Registrados'),          'count' => $total,          'pct' => 100],
                ['label' => __('Email verificado'),      'count' => $verified,       'pct' => $total > 0 ? round($verified / $total * 100) : 0],
                ['label' => __('Primera parcela'),       'count' => $withPlots,      'pct' => $total > 0 ? round($withPlots / $total * 100) : 0],
                ['label' => __('Primera actividad'),     'count' => $withActivities, 'pct' => $total > 0 ? round($withActivities / $total * 100) : 0],
                ['label' => __('Activo últimos 30 días'), 'count' => $active30d,      'pct' => $total > 0 ? round($active30d / $total * 100) : 0],
            ];
        } catch (\Throwable) {
            return [];
        }
    }

    private function buildSuspiciousUsers(): \Illuminate\Support\Collection
    {
        try {
            $since = now()->subHours(24);

            return SecurityEvent::where('event', 'failed_login')
                ->where('created_at', '>=', $since)
                ->whereNotNull('email')
                ->selectRaw('email, COUNT(*) as attempts, MAX(created_at) as last_attempt, MAX(ip) as ip')
                ->groupBy('email')
                ->having('attempts', '>=', 3)
                ->orderByDesc('attempts')
                ->limit(5)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
    }

    private function orphanedPlotsCount(): int
    {
        try {
            return (int) DB::table('plots')
                ->leftJoin('users', 'users.id', '=', 'plots.viticulturist_id')
                ->where(function ($q) {
                    $q->whereNull('users.id')
                        ->orWhere('users.can_login', false);
                })
                ->count();
        } catch (\Throwable) {
            return 0;
        }
    }
}
