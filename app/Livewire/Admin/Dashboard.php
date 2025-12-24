<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\Plot;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\AgriculturalActivity;
use App\Models\SupportTicket;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // Estadísticas generales del sistema
        $stats = [
            'users' => [
                'total' => User::count(),
                'by_role' => [
                    'admin' => User::where('role', 'admin')->count(),
                    'supervisor' => User::where('role', 'supervisor')->count(),
                    'winery' => User::where('role', 'winery')->count(),
                    'viticulturist' => User::where('role', 'viticulturist')->count(),
                ],
                'active' => User::where('can_login', true)->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'new_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'plots' => [
                'total' => Plot::count(),
                'total_area' => Plot::sum('area') ?? 0,
                'new_this_month' => Plot::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'clients' => [
                'total' => Client::count(),
                'active' => Client::where('active', true)->count(),
                'individual' => Client::where('client_type', 'individual')->count(),
                'company' => Client::where('client_type', 'company')->count(),
            ],
            'invoices' => [
                'total' => Invoice::count(),
                'this_year' => Invoice::whereYear('invoice_date', now()->year)->count(),
                'this_year_amount' => Invoice::whereYear('invoice_date', now()->year)
                    ->sum('total_amount') ?? 0,
                'pending' => Invoice::where('payment_status', 'unpaid')
                    ->where('status', '!=', 'cancelled')
                    ->count(),
            ],
            'activities' => [
                'total' => AgriculturalActivity::count(),
                'this_year' => AgriculturalActivity::whereYear('activity_date', now()->year)->count(),
                'this_month' => AgriculturalActivity::whereYear('activity_date', now()->year)
                    ->whereMonth('activity_date', now()->month)
                    ->count(),
            ],
            'support' => [
                'total' => SupportTicket::count(),
                'open' => SupportTicket::open()->count(),
                'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::where('status', 'resolved')->count(),
                'new_this_week' => SupportTicket::where('created_at', '>=', now()->subWeek())->count(),
            ],
        ];

        return view('livewire.admin.dashboard', [
            'stats' => $stats,
        ])->layout('layouts.app', [
            'title' => 'Dashboard Administrador - Agro365',
            'description' => 'Panel de control con estadísticas generales del sistema',
        ]);
    }
}

