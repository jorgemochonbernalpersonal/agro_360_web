<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Client;
use App\Models\Container;
use App\Models\Crew;
use App\Models\Invoice;
use App\Models\Plot;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;

class UserStatisticsService
{
    public function getStatisticsForUser(User $user): array
    {
        $stats = [
            'basic' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
                'can_login' => $user->can_login,
                'is_beta_user' => $user->is_beta_user,
                'beta_ends_at' => $user->beta_ends_at,
            ],
        ];

        switch ($user->role) {
            case 'viticulturist':
                $stats['viticulturist'] = $this->getViticulturistStats($user);
                break;
            case 'winery':
                $stats['winery'] = $this->getWineryStats($user);
                break;
            case 'supervisor':
                $stats['supervisor'] = $this->getSupervisorStats($user);
                break;
            case 'admin':
                $stats['admin'] = $this->getAdminStats($user);
                break;
            case 'producer':
                $stats['viticulturist'] = $this->getViticulturistStats($user);
                $stats['winery'] = $this->getWineryStats($user);
                break;
        }

        return $stats;
    }

    private function getViticulturistStats(User $user): array
    {
        return [
            'plots' => [
                'total' => Plot::forUser($user)->count(),
                'total_area' => Plot::forUser($user)->sum('area'),
            ],
            'clients' => [
                'total' => Client::forUser($user->id)->count(),
                'active' => Client::forUser($user->id)->where('active', true)->count(),
                'individual' => Client::forUser($user->id)->where('client_type', 'individual')->count(),
                'company' => Client::forUser($user->id)->where('client_type', 'company')->count(),
            ],
            'invoices' => [
                'total' => Invoice::forUser($user->id)->count(),
                'this_year' => Invoice::forUser($user->id)->whereYear('invoice_date', now()->year)->count(),
                'total_amount' => Invoice::forUser($user->id)->sum('total_amount'),
                'this_year_amount' => Invoice::forUser($user->id)->whereYear('invoice_date', now()->year)->sum('total_amount'),
            ],
            'activities' => [
                'total' => AgriculturalActivity::forUser($user->id)->count(),
                'this_year' => AgriculturalActivity::forUser($user->id)->whereYear('activity_date', now()->year)->count(),
                'this_month' => AgriculturalActivity::forUser($user->id)
                    ->whereYear('activity_date', now()->year)
                    ->whereMonth('activity_date', now()->month)
                    ->count(),
            ],
            'campaigns' => [
                'total' => Campaign::where('viticulturist_id', $user->id)->count(),
                'active' => Campaign::where('viticulturist_id', $user->id)->where('active', true)->count(),
            ],
        ];
    }

    private function getWineryStats(User $user): array
    {
        $uid = $user->id;
        $year = now()->year;

        $invoiceRaw = DB::table('invoices')
            ->where('user_id', $uid)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN YEAR(invoice_date) = {$year} THEN 1 ELSE 0 END) as this_year")
            ->selectRaw('COALESCE(SUM(total_amount), 0) as total_amount')
            ->selectRaw("COALESCE(SUM(CASE WHEN YEAR(invoice_date) = {$year} THEN total_amount ELSE 0 END), 0) as this_year_amount")
            ->selectRaw("SUM(CASE WHEN payment_status = 'unpaid' AND status != 'cancelled' THEN 1 ELSE 0 END) as pending")
            ->first();

        $wineRaw = DB::table('wines')
            ->where('user_id', $uid)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'bottled' THEN 1 ELSE 0 END) as bottled")
            ->selectRaw('COALESCE(SUM(volume_liters), 0) as total_liters')
            ->first();

        return [
            'viticulturists' => [
                'total' => WineryViticulturist::where('winery_id', $uid)->count(),
            ],
            'crews' => [
                'total' => Crew::where('winery_id', $uid)->count(),
            ],
            'clients' => [
                'total' => Client::forUser($uid)->count(),
                'active' => Client::forUser($uid)->where('active', true)->count(),
            ],
            'invoices' => [
                'total' => (int) $invoiceRaw->total,
                'this_year' => (int) $invoiceRaw->this_year,
                'total_amount' => (float) $invoiceRaw->total_amount,
                'this_year_amount' => (float) $invoiceRaw->this_year_amount,
                'pending' => (int) $invoiceRaw->pending,
            ],
            'wines' => [
                'total' => (int) $wineRaw->total,
                'in_progress' => (int) $wineRaw->in_progress,
                'bottled' => (int) $wineRaw->bottled,
                'total_liters' => (float) $wineRaw->total_liters,
            ],
            'containers' => [
                'total' => Container::where('user_id', $uid)->count(),
                'active' => Container::where('user_id', $uid)->where('archived', false)->count(),
            ],
        ];
    }

    private function getSupervisorStats(User $user): array
    {
        $wineryIds = SupervisorWinery::where('supervisor_id', $user->id)->pluck('winery_id');
        $vitIds = SupervisorViticulturist::where('supervisor_id', $user->id)->pluck('viticulturist_id');

        return [
            'wineries' => [
                'total' => $wineryIds->count(),
                'active' => User::whereIn('id', $wineryIds)->where('can_login', true)->count(),
                'inactive' => User::whereIn('id', $wineryIds)->where('can_login', false)->count(),
            ],
            'viticulturists' => [
                'total' => $vitIds->count(),
                'active' => User::whereIn('id', $vitIds)->where('can_login', true)->count(),
                'inactive' => User::whereIn('id', $vitIds)->where('can_login', false)->count(),
            ],
        ];
    }

    private function getAdminStats(User $user): array
    {
        $now = now();
        $year = $now->year;
        $month = $now->month;

        $userRaw = DB::table('users')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN can_login = 1 THEN 1 ELSE 0 END) as active')
            ->selectRaw("SUM(CASE WHEN MONTH(created_at) = {$month} AND YEAR(created_at) = {$year} THEN 1 ELSE 0 END) as new_this_month")
            ->first();

        $ticketRaw = DB::table('support_tickets')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status IN ('open','in_progress') THEN 1 ELSE 0 END) as open")
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_week', [$now->copy()->subWeek()])
            ->first();

        return [
            'users' => [
                'total' => (int) $userRaw->total,
                'active' => (int) $userRaw->active,
                'new_this_month' => (int) $userRaw->new_this_month,
            ],
            'plots' => [
                'total' => (int) DB::table('plots')->count(),
                'total_area' => (float) DB::table('plots')->sum('area'),
            ],
            'support' => [
                'total' => (int) $ticketRaw->total,
                'open' => (int) $ticketRaw->open,
                'new_this_week' => (int) $ticketRaw->new_this_week,
            ],
            'invoices' => [
                'pending' => (int) DB::table('invoices')
                    ->where('payment_status', 'unpaid')
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'this_year_amount' => (float) DB::table('invoices')
                    ->whereYear('invoice_date', $year)
                    ->sum('total_amount'),
            ],
        ];
    }
}
