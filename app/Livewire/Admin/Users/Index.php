<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public $currentTab = 'all';
    public $search = '';
    public $filterActive = '';
    public $filterVerified = '';
    public $filterBeta = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab', 'except' => 'all'],
        'search' => ['except' => ''],
        'filterActive' => ['except' => ''],
        'filterVerified' => ['except' => ''],
        'filterBeta' => ['except' => ''],
    ];

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterActive()
    {
        $this->resetPage();
    }

    public function updatingFilterVerified()
    {
        $this->resetPage();
    }

    public function updatingFilterBeta()
    {
        $this->resetPage();
    }

    public function impersonate($userId)
    {
        // Solo admins pueden impersonar
        if (!Auth::user()->isAdmin()) {
            $this->toastError('No tienes permiso para impersonar usuarios.');
            return;
        }

        $targetUser = User::findOrFail($userId);

        // No permitir impersonar a otro admin
        if ($targetUser->isAdmin()) {
            $this->toastError('No puedes impersonar a otro administrador por razones de seguridad.');
            return;
        }

        // Solo permitir impersonar usuarios activos
        if (!$targetUser->can_login) {
            $this->toastError('No puedes impersonar usuarios inactivos. Activa el usuario primero.');
            return;
        }

        // Guardar admin original en sesión
        session()->put('impersonating', true);
        session()->put('admin_id', Auth::id());
        session()->put('admin_name', Auth::user()->name);

        // Log de seguridad
        SecurityLogger::logImpersonation(Auth::id(), $targetUser->id);

        // Hacer login como el usuario objetivo
        Auth::login($targetUser);
        session()->regenerate();

        // Redirigir al dashboard del usuario
        $dashboardRoute = match($targetUser->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            default => 'home',
        };

        $this->toastSuccess("Ahora estás viendo como: {$targetUser->name}");
        return $this->redirect(route($dashboardRoute), navigate: true);
    }

    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);
        
        // No permitir desactivar a otro admin
        if ($user->isAdmin() && $user->id !== Auth::id()) {
            $this->toastError('No puedes desactivar a otro administrador.');
            return;
        }

        $user->can_login = !$user->can_login;
        $user->save();

        $status = $user->can_login ? 'activado' : 'desactivado';
        $this->toastSuccess("Usuario {$status} exitosamente.");
    }

    public function toggleBeta($userId)
    {
        $user = User::findOrFail($userId);
        
        $user->is_beta_user = !$user->is_beta_user;
        if (!$user->is_beta_user) {
            $user->beta_ends_at = null;
        }
        $user->save();

        $status = $user->is_beta_user ? 'con acceso beta' : 'sin acceso beta';
        $this->toastSuccess("Usuario {$status}.");
    }

    public function render()
    {
        $query = User::query();

        // Filtrar por rol según tab
        if ($this->currentTab !== 'all') {
            $query->where('role', $this->currentTab);
        }

        // Búsqueda
        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        // Filtros
        if ($this->filterActive !== '') {
            $query->where('can_login', $this->filterActive === '1');
        }

        if ($this->filterVerified !== '') {
            if ($this->filterVerified === '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        if ($this->filterBeta !== '') {
            if ($this->filterBeta === 'active') {
                $query->where('is_beta_user', true)
                      ->where(function($q) {
                          $q->whereNull('beta_ends_at')
                            ->orWhere('beta_ends_at', '>', now());
                      });
            } elseif ($this->filterBeta === 'expired') {
                $query->where('is_beta_user', true)
                      ->where('beta_ends_at', '<=', now());
            } elseif ($this->filterBeta === 'never') {
                $query->where('is_beta_user', false);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        // Estadísticas
        $stats = [
            'total' => User::count(),
            'by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'supervisor' => User::where('role', 'supervisor')->count(),
                'winery' => User::where('role', 'winery')->count(),
                'viticulturist' => User::where('role', 'viticulturist')->count(),
            ],
            'active' => User::where('can_login', true)->count(),
            'inactive' => User::where('can_login', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'beta_active' => User::where('is_beta_user', true)
                ->where(function($q) {
                    $q->whereNull('beta_ends_at')
                      ->orWhere('beta_ends_at', '>', now());
                })->count(),
            'beta_expired' => User::where('is_beta_user', true)
                ->where('beta_ends_at', '<=', now())->count(),
        ];

        return view('livewire.admin.users.index', [
            'users' => $users,
            'stats' => $stats,
        ])->layout('layouts.app');
    }
}

