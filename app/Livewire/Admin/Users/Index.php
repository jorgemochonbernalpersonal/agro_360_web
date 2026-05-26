<?php

namespace App\Livewire\Admin\Users;

use App\Models\AppSetting;
use App\Models\User;
use App\Livewire\Concerns\WithToastNotifications;
use App\Services\SecurityLogger;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    // Filters
    public $currentTab = 'all';
    public $search = '';
    public $filterActive = '';
    public $filterVerified = '';
    public $filterBeta = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public bool $showInternal = false;

    // Create modal
    public $showCreateModal = false;
    public $createName = '';
    public $createEmail = '';
    public $createRole = 'viticulturist';
    public $createPassword = '';
    public $createForceReset = false;
    public $createSendVerification = true;

    // Beta date from settings
    public $betaEndsAt = '';

    public function mount(): void
    {
        $this->betaEndsAt = AppSetting::get('beta_end_date', '2026-06-30');
    }

    // Bulk operations
    public $selectedUsers = [];

    protected $queryString = [
        'currentTab'     => ['as' => 'tab', 'except' => 'all'],
        'search'         => ['except' => ''],
        'filterActive'   => ['except' => ''],
        'filterVerified' => ['except' => ''],
        'filterBeta'     => ['except' => ''],
        'filterDateFrom' => ['except' => '', 'as' => 'from'],
        'filterDateTo'   => ['except' => '', 'as' => 'to'],
        'showInternal'   => ['except' => false, 'as' => 'internal'],
    ];

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
        $this->resetPage();
        $this->selectedUsers = [];
    }

    public function updatingSearch()         { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingFilterActive()   { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingFilterVerified() { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingFilterBeta()     { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingFilterDateFrom() { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingFilterDateTo()   { $this->resetPage(); $this->selectedUsers = []; }
    public function updatingShowInternal()   { $this->resetPage(); $this->selectedUsers = []; }

    public function toggleInternal(): void
    {
        $this->showInternal = !$this->showInternal;
        $this->resetPage();
        $this->selectedUsers = [];
    }

    // ─── Create ───────────────────────────────────────────────────────────────

    public function openCreateModal()
    {
        $this->reset(['createName', 'createEmail', 'createPassword', 'createForceReset']);
        $this->createRole = 'viticulturist';
        $this->createSendVerification = true;
        $this->showCreateModal = true;
        $this->resetValidation();
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->validate([
            'createName'     => 'required|string|max:255',
            'createEmail'    => 'required|email|unique:users,email',
            'createRole'     => 'required|in:admin,supervisor,winery,viticulturist,producer',
            'createPassword' => 'required|string|min:8',
        ], [
            'createName.required'     => __('El nombre es obligatorio.'),
            'createEmail.required'    => __('El email es obligatorio.'),
            'createEmail.unique'      => __('Ya existe un usuario con este email.'),
            'createRole.required'     => __('El rol es obligatorio.'),
            'createPassword.required' => __('La contraseña es obligatoria.'),
            'createPassword.min'      => __('La contraseña debe tener al menos 8 caracteres.'),
        ]);

        $user = User::create([
            'name'               => $this->createName,
            'email'              => $this->createEmail,
            'role'               => $this->createRole,
            'password'           => $this->createPassword,
            'can_login'          => true,
            'password_must_reset'=> $this->createForceReset,
        ]);

        if ($this->createSendVerification) {
            $user->sendEmailVerificationNotification();
        } else {
            $user->email_verified_at = now();
            $user->save();
        }

        SecurityLogger::logSecurityEvent('user_created_by_admin', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'role'       => $user->role,
        ]);

        $this->closeCreateModal();
        $this->toastSuccess("Usuario {$user->name} creado correctamente.");
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function deleteUser($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->isAdmin()) {
            $this->toastError(__('No puedes eliminar a un administrador.'));
            return;
        }

        if ($user->id === Auth::id()) {
            $this->toastError(__('No puedes eliminarte a ti mismo.'));
            return;
        }

        $name = $user->name;

        SecurityLogger::logSecurityEvent('user_deleted_by_admin', [
            'admin_id'   => Auth::id(),
            'user_id'    => $userId,
            'user_email' => $user->email,
        ]);

        $user->delete();
        $this->toastSuccess("Usuario {$name} eliminado.");
    }

    // ─── Bulk operations ──────────────────────────────────────────────────────

    public function clearSelection()
    {
        $this->selectedUsers = [];
    }

    public function bulkActivate()
    {
        if (empty($this->selectedUsers)) return;

        $count = User::whereIn('id', $this->selectedUsers)
            ->where('role', '!=', 'admin')
            ->update(['can_login' => true]);

        $this->selectedUsers = [];
        $this->toastSuccess("{$count} usuario(s) activados.");
    }

    public function bulkDeactivate()
    {
        if (empty($this->selectedUsers)) return;

        $count = User::whereIn('id', $this->selectedUsers)
            ->where('role', '!=', 'admin')
            ->update(['can_login' => false]);

        $this->selectedUsers = [];
        $this->toastSuccess("{$count} usuario(s) desactivados.");
    }

    public function bulkEnableBeta()
    {
        if (empty($this->selectedUsers)) return;

        $betaDate = Carbon::parse($this->betaEndsAt . ' 23:59:59');

        $count = User::whereIn('id', $this->selectedUsers)
            ->where('role', '!=', 'admin')
            ->update([
                'is_beta_user'         => true,
                'beta_ends_at'         => $betaDate,
                'beta_access_granted'  => true,
            ]);

        $this->selectedUsers = [];
        $this->toastSuccess("Beta activado para {$count} usuario(s).");
    }

    public function bulkDisableBeta()
    {
        if (empty($this->selectedUsers)) return;

        $count = User::whereIn('id', $this->selectedUsers)
            ->where('role', '!=', 'admin')
            ->update([
                'is_beta_user'        => false,
                'beta_ends_at'        => null,
            ]);

        $this->selectedUsers = [];
        $this->toastSuccess("Beta desactivado para {$count} usuario(s).");
    }

    // ─── Impersonate / Toggle ─────────────────────────────────────────────────

    public function impersonate($userId)
    {
        if (!Auth::user()->isAdmin()) {
            $this->toastError(__('No tienes permiso para impersonar usuarios.'));
            return;
        }

        $targetUser = User::findOrFail($userId);

        if ($targetUser->isAdmin()) {
            $this->toastError(__('No puedes impersonar a otro administrador por razones de seguridad.'));
            return;
        }

        if (!$targetUser->can_login) {
            $this->toastError(__('No puedes impersonar usuarios inactivos. Activa el usuario primero.'));
            return;
        }

        session()->put('impersonating', true);
        session()->put('admin_id', Auth::id());
        session()->put('admin_name', Auth::user()->name);
        session()->put('impersonation_started_at', now()->timestamp);

        SecurityLogger::logImpersonation(Auth::id(), $targetUser->id);

        Auth::login($targetUser);
        session()->regenerate();

        $dashboardRoute = match($targetUser->role) {
            'admin'         => 'admin.dashboard',
            'supervisor'    => 'supervisor.dashboard',
            'winery'        => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            'producer'      => 'producer.dashboard',
            default         => 'home',
        };

        $this->toastSuccess("Ahora estás viendo como: {$targetUser->name}");
        return $this->redirect(route($dashboardRoute), navigate: true);
    }

    public function toggleActive($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->isAdmin() && $user->id !== Auth::id()) {
            $this->toastError(__('No puedes desactivar a otro administrador.'));
            return;
        }

        $user->can_login = !$user->can_login;
        $user->save();

        $status = $user->can_login ? 'activado' : 'desactivado';
        SecurityLogger::logSecurityEvent('user_account_toggled', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'user_email' => $user->email,
            'action'     => $status,
        ]);
        $this->toastSuccess("Usuario {$status} exitosamente.");
    }

    public function toggleBeta($userId)
    {
        $user     = User::findOrFail($userId);
        $enabling = !$user->is_beta_user;
        $betaDate = Carbon::parse($this->betaEndsAt . ' 23:59:59');
        $cascadeCount = 0;

        DB::transaction(function () use ($user, $enabling, $betaDate, &$cascadeCount) {
            $user->is_beta_user = $enabling;
            if ($enabling) {
                $user->beta_ends_at        = $betaDate;
                $user->beta_access_granted = true;
            } else {
                $user->beta_ends_at = null;
            }
            $user->save();

            if ($enabling && $user->hasWineryAccess()) {
                $viticulturistIds = DB::table('winery_viticulturist')
                    ->where('winery_id', $user->id)
                    ->pluck('viticulturist_id');

                if ($viticulturistIds->isNotEmpty()) {
                    $cascadeCount = User::whereIn('id', $viticulturistIds)
                        ->where('is_beta_user', false)
                        ->update([
                            'is_beta_user'         => true,
                            'beta_ends_at'         => $betaDate,
                            'beta_access_granted'  => true,
                        ]);
                }
            }
        });

        SecurityLogger::logSecurityEvent('user_beta_toggled', [
            'admin_id'      => Auth::id(),
            'user_id'       => $user->id,
            'user_email'    => $user->email,
            'action'        => $enabling ? 'enabled' : 'disabled',
            'cascade_count' => $cascadeCount,
        ]);

        $status  = $enabling ? 'con acceso beta' : 'sin acceso beta';
        $message = "Usuario {$status}.";
        if ($cascadeCount > 0) {
            $message .= " Beta activada también para {$cascadeCount} viticultor(es) vinculados.";
        }
        $this->toastSuccess($message);
    }

    // ─── Filtered query (shared by render + export) ───────────────────────────

    private function buildFilteredQuery()
    {
        $query = $this->showInternal ? User::query() : User::excludeDemo();

        if ($this->currentTab !== 'all') {
            $query->where('role', $this->currentTab);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

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
                      ->where(function ($q) {
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

        if ($this->filterDateFrom !== '') {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo !== '') {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        return $query;
    }

    // ─── Export ───────────────────────────────────────────────────────────────

    public function exportCsv()
    {
        $activeFilters = array_filter([
            $this->currentTab !== 'all' ? $this->currentTab : null,
            $this->search,
            $this->filterActive,
            $this->filterVerified,
            $this->filterBeta,
            $this->filterDateFrom,
            $this->filterDateTo,
        ]);

        $users    = $this->buildFilteredQuery()->orderBy('created_at', 'desc')->get();
        $filename = 'usuarios_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($users) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel
            fputcsv($handle, ['ID', 'Nombre', 'Email', 'Rol', 'Estado', 'Email Verificado', 'Beta', 'Fin Beta', 'Registro']);

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
                    $user->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $users = $this->buildFilteredQuery()->orderBy('created_at', 'desc')->paginate(20);

        // Stats: siempre sobre usuarios reales (excluye internos), query única optimizada
        $raw = User::excludeDemo()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as role_admin")
            ->selectRaw("SUM(CASE WHEN role = 'supervisor' THEN 1 ELSE 0 END) as role_supervisor")
            ->selectRaw("SUM(CASE WHEN role = 'winery' THEN 1 ELSE 0 END) as role_winery")
            ->selectRaw("SUM(CASE WHEN role = 'viticulturist' THEN 1 ELSE 0 END) as role_viticulturist")
            ->selectRaw("SUM(CASE WHEN role = 'producer' THEN 1 ELSE 0 END) as role_producer")
            ->selectRaw("SUM(CASE WHEN can_login = 1 THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN can_login = 0 THEN 1 ELSE 0 END) as inactive")
            ->selectRaw("SUM(CASE WHEN email_verified_at IS NOT NULL THEN 1 ELSE 0 END) as verified")
            ->selectRaw("SUM(CASE WHEN email_verified_at IS NULL THEN 1 ELSE 0 END) as unverified")
            ->selectRaw("SUM(CASE WHEN is_beta_user = 1 AND (beta_ends_at IS NULL OR beta_ends_at > NOW()) THEN 1 ELSE 0 END) as beta_active")
            ->selectRaw("SUM(CASE WHEN is_beta_user = 1 AND beta_ends_at <= NOW() THEN 1 ELSE 0 END) as beta_expired")
            ->first();

        $stats = [
            'total'   => (int) $raw->total,
            'by_role' => [
                'admin'         => (int) $raw->role_admin,
                'supervisor'    => (int) $raw->role_supervisor,
                'winery'        => (int) $raw->role_winery,
                'viticulturist' => (int) $raw->role_viticulturist,
                'producer'      => (int) $raw->role_producer,
            ],
            'active'       => (int) $raw->active,
            'inactive'     => (int) $raw->inactive,
            'verified'     => (int) $raw->verified,
            'unverified'   => (int) $raw->unverified,
            'beta_active'  => (int) $raw->beta_active,
            'beta_expired' => (int) $raw->beta_expired,
        ];

        return view('livewire.admin.users.index', [
            'users'        => $users,
            'stats'        => $stats,
            'pageUserIds'  => $users->pluck('id')->toArray(),
        ])->layout('layouts.app');
    }
}
