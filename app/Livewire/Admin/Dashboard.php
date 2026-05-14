<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Plot;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\AgriculturalActivity;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
    public function exportCsv()
    {
        $users    = User::excludeDemo()->orderBy('created_at', 'desc')->get();
        $filename = 'usuarios_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
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

    public function render()
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        // Optimized: batch user counts in a single query
        $userStats = DB::table('users')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin")
            ->selectRaw("SUM(CASE WHEN role = 'supervisor' THEN 1 ELSE 0 END) as supervisor")
            ->selectRaw("SUM(CASE WHEN role = 'winery' THEN 1 ELSE 0 END) as winery")
            ->selectRaw("SUM(CASE WHEN role = 'viticulturist' THEN 1 ELSE 0 END) as viticulturist")
            ->selectRaw("SUM(CASE WHEN role = 'producer' THEN 1 ELSE 0 END) as producer")
            ->selectRaw("SUM(CASE WHEN can_login = 1 THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified")
            ->selectRaw("SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month", [$month, $year])
            ->first();

        // Optimized: batch plot counts
        $plotStats = DB::table('plots')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("COALESCE(SUM(area), 0) as total_area")
            ->selectRaw("SUM(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? THEN 1 ELSE 0 END) as new_this_month", [$month, $year])
            ->first();

        // Optimized: batch client counts
        $clientStats = DB::table('clients')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN active = 1 THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN client_type = 'individual' THEN 1 ELSE 0 END) as individual")
            ->selectRaw("SUM(CASE WHEN client_type = 'company' THEN 1 ELSE 0 END) as company")
            ->first();

        // Optimized: batch invoice counts
        $invoiceStats = DB::table('invoices')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN YEAR(invoice_date) = ? THEN 1 ELSE 0 END) as this_year", [$year])
            ->selectRaw("COALESCE(SUM(CASE WHEN YEAR(invoice_date) = ? THEN total_amount ELSE 0 END), 0) as this_year_amount", [$year])
            ->selectRaw("SUM(CASE WHEN payment_status = 'unpaid' AND status != 'cancelled' THEN 1 ELSE 0 END) as pending")
            ->first();

        // Optimized: batch activity counts
        $activityStats = DB::table('agricultural_activities')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN YEAR(activity_date) = ? THEN 1 ELSE 0 END) as this_year", [$year])
            ->selectRaw("SUM(CASE WHEN YEAR(activity_date) = ? AND MONTH(activity_date) = ? THEN 1 ELSE 0 END) as this_month", [$year, $month])
            ->first();

        // Optimized: batch support ticket counts
        $supportStats = DB::table('support_tickets')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status IN ('open', 'in_progress') THEN 1 ELSE 0 END) as open")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved")
            ->selectRaw("SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_week", [$now->subWeek()])
            ->first();

        $stats = [
            'users' => [
                'total'          => (int) $userStats->total,
                'by_role'        => [
                    'admin'         => (int) $userStats->admin,
                    'supervisor'    => (int) $userStats->supervisor,
                    'winery'        => (int) $userStats->winery,
                    'viticulturist' => (int) $userStats->viticulturist,
                    'producer'      => (int) $userStats->producer,
                ],
                'active'         => (int) $userStats->active,
                'verified'       => (int) $userStats->verified,
                'new_this_month' => (int) $userStats->new_this_month,
            ],
            'plots' => [
                'total'          => (int) $plotStats->total,
                'total_area'     => (float) $plotStats->total_area,
                'new_this_month' => (int) $plotStats->new_this_month,
            ],
            'clients' => [
                'total'      => (int) $clientStats->total,
                'active'     => (int) $clientStats->active,
                'individual' => (int) $clientStats->individual,
                'company'    => (int) $clientStats->company,
            ],
            'invoices' => [
                'total'            => (int) $invoiceStats->total,
                'this_year'        => (int) $invoiceStats->this_year,
                'this_year_amount' => (float) $invoiceStats->this_year_amount,
                'pending'          => (int) $invoiceStats->pending,
            ],
            'activities' => [
                'total'      => (int) $activityStats->total,
                'this_year'  => (int) $activityStats->this_year,
                'this_month' => (int) $activityStats->this_month,
            ],
            'support' => [
                'total'         => (int) $supportStats->total,
                'open'          => (int) $supportStats->open,
                'in_progress'   => (int) $supportStats->in_progress,
                'resolved'      => (int) $supportStats->resolved,
                'new_this_week' => (int) $supportStats->new_this_week,
            ],
        ];

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Dashboard Administrador - Agro365',
            'description' => 'Panel de control con estadísticas generales del sistema',
        ]);
    }
}
