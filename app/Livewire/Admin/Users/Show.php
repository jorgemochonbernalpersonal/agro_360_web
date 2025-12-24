<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Plot;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\SupervisorWinery;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithToastNotifications;

    public User $user;
    public $user_id;
    public $stats = [];

    public function mount($user)
    {
        // Si es un modelo, usarlo directamente; si es un ID, buscarlo
        if ($user instanceof User) {
            $this->user = $user;
            $this->user_id = $user->id;
        } else {
            $this->user_id = $user;
            $this->user = User::findOrFail($this->user_id);
        }
        
        $this->loadStats();
    }

    public function loadStats()
    {
        $this->stats = $this->getUserStatistics($this->user);
    }

    private function getUserStatistics(User $user): array
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

        // Estadísticas según el rol
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
        }

        return $stats;
    }

    private function getViticulturistStats(User $user): array
    {
        return [
            'plots' => [
                'total' => Plot::forUser($user->id)->count(),
                'total_area' => Plot::forUser($user->id)->sum('area') ?? 0,
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
                'total_amount' => Invoice::forUser($user->id)->sum('total_amount') ?? 0,
                'this_year_amount' => Invoice::forUser($user->id)->whereYear('invoice_date', now()->year)->sum('total_amount') ?? 0,
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
        return [
            'viticulturists' => [
                'total' => WineryViticulturist::where('winery_id', $user->id)->count(),
            ],
            'crews' => [
                'total' => Crew::where('winery_id', $user->id)->count(),
            ],
        ];
    }

    private function getSupervisorStats(User $user): array
    {
        return [
            'wineries' => [
                'total' => SupervisorWinery::where('supervisor_id', $user->id)->count(),
            ],
            'viticulturists' => [
                'total' => SupervisorViticulturist::where('supervisor_id', $user->id)->count(),
            ],
        ];
    }

    private function getAdminStats(User $user): array
    {
        return [
            'note' => 'Los administradores tienen acceso completo al sistema',
        ];
    }

    public function impersonate()
    {
        // Solo admins pueden impersonar
        if (!Auth::user()->isAdmin()) {
            $this->toastError('No tienes permiso para impersonar usuarios.');
            return;
        }

        // No permitir impersonar a otro admin
        if ($this->user->isAdmin()) {
            $this->toastError('No puedes impersonar a otro administrador por razones de seguridad.');
            return;
        }

        // Solo permitir impersonar usuarios activos
        if (!$this->user->can_login) {
            $this->toastError('No puedes impersonar usuarios inactivos. Activa el usuario primero.');
            return;
        }

        // Guardar admin original en sesión
        session()->put('impersonating', true);
        session()->put('admin_id', Auth::id());
        session()->put('admin_name', Auth::user()->name);

        // Log de seguridad
        SecurityLogger::logImpersonation(Auth::id(), $this->user->id);

        // Hacer login como el usuario objetivo
        Auth::login($this->user);
        session()->regenerate();

        // Redirigir al dashboard del usuario
        $dashboardRoute = match($this->user->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            default => 'home',
        };

        $this->toastSuccess("Ahora estás viendo como: {$this->user->name}");
        return $this->redirect(route($dashboardRoute), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.users.show', [
            'stats' => $this->stats,
        ])->layout('layouts.app', [
            'title' => $this->user->name . ' - Usuario - Agro365',
            'description' => 'Detalles del usuario ' . $this->user->name . '. Información, estadísticas y actividad.',
        ]);
    }
}

