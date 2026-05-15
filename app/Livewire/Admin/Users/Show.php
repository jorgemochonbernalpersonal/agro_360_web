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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public User $user;
    public $user_id;
    public $stats     = [];
    public $hierarchy = [];

    // Edit modal
    public $showEditModal = false;
    public $editName  = '';
    public $editEmail = '';
    public $editRole  = '';

    public function mount($user)
    {
        if ($user instanceof User) {
            $this->user    = $user;
            $this->user_id = $user->id;
        } else {
            $this->user_id = $user;
            $this->user    = User::with('organization')->findOrFail($this->user_id);
        }

        $this->loadStats();
        $this->loadHierarchy();
    }

    public function loadStats()
    {
        $this->stats = $this->getUserStatistics($this->user);
    }

    // ─── Hierarchy ────────────────────────────────────────────────────────────

    private function loadHierarchy()
    {
        $hierarchy = [];

        if ($this->user->hasWineryAccess()) {
            $ids = WineryViticulturist::where('winery_id', $this->user->id)->pluck('viticulturist_id');
            $hierarchy['viticulturists'] = User::whereIn('id', $ids)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'can_login']);
        }

        if ($this->user->hasViticulturistAccess()) {
            $ids = WineryViticulturist::where('viticulturist_id', $this->user->id)->pluck('winery_id');
            $hierarchy['wineries'] = User::whereIn('id', $ids)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'can_login']);
        }

        if ($this->user->isSupervisor()) {
            $wineryIds = SupervisorWinery::where('supervisor_id', $this->user->id)->pluck('winery_id');
            $hierarchy['supervised_wineries'] = User::whereIn('id', $wineryIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'can_login']);

            $vitIds = SupervisorViticulturist::where('supervisor_id', $this->user->id)->pluck('viticulturist_id');
            $hierarchy['supervised_viticulturists'] = User::whereIn('id', $vitIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'can_login']);
        }

        $this->hierarchy = $hierarchy;
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function openEditModal()
    {
        $this->editName  = $this->user->name;
        $this->editEmail = $this->user->email;
        $this->editRole  = $this->user->role;
        $this->showEditModal = true;
        $this->resetValidation();
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function saveUser()
    {
        $this->validate([
            'editName'  => 'required|string|max:255',
            'editEmail' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'editRole'  => 'required|in:admin,supervisor,winery,viticulturist,producer',
        ], [
            'editName.required'  => 'El nombre es obligatorio.',
            'editEmail.required' => 'El email es obligatorio.',
            'editEmail.unique'   => 'Ya existe un usuario con este email.',
            'editRole.required'  => 'El rol es obligatorio.',
        ]);

        $emailChanged = $this->editEmail !== $this->user->email;

        $this->user->name  = $this->editName;
        $this->user->email = $this->editEmail;
        $this->user->role  = $this->editRole;

        if ($emailChanged) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();
        $this->user->refresh();

        SecurityLogger::logSecurityEvent('user_edited_by_admin', [
            'admin_id'      => Auth::id(),
            'user_id'       => $this->user->id,
            'email_changed' => $emailChanged,
        ]);

        $this->loadStats();
        $this->loadHierarchy();
        $this->closeEditModal();
        $this->toastSuccess('Usuario actualizado correctamente.');
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function deleteUser()
    {
        if ($this->user->isAdmin()) {
            $this->toastError('No puedes eliminar a un administrador.');
            return;
        }

        if ($this->user->id === Auth::id()) {
            $this->toastError('No puedes eliminarte a ti mismo.');
            return;
        }

        $name = $this->user->name;

        SecurityLogger::logSecurityEvent('user_deleted_by_admin', [
            'admin_id'   => Auth::id(),
            'user_id'    => $this->user->id,
            'user_email' => $this->user->email,
        ]);

        $this->user->delete();
        $this->toastSuccess("Usuario {$name} eliminado.");
        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    // ─── Password reset ───────────────────────────────────────────────────────

    public function sendPasswordReset()
    {
        $status = Password::sendResetLink(['email' => $this->user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->toastSuccess('Email de restablecimiento enviado a ' . $this->user->email . '.');
        } else {
            $this->toastError('No se pudo enviar el email. Verifica que el email existe y es válido.');
        }
    }

    // ─── Verify email manually ────────────────────────────────────────────────

    public function verifyEmailManually()
    {
        if ($this->user->email_verified_at) {
            $this->toastError('El email ya está verificado.');
            return;
        }

        $this->user->email_verified_at = now();
        $this->user->save();
        $this->user->refresh();

        SecurityLogger::logSecurityEvent('email_verified_manually_by_admin', [
            'admin_id' => Auth::id(),
            'user_id'  => $this->user->id,
        ]);

        $this->loadStats();
        $this->toastSuccess('Email verificado manualmente.');
    }

    // ─── Toggle active ────────────────────────────────────────────────────────

    public function toggleActive()
    {
        if ($this->user->isAdmin() && $this->user->id !== Auth::id()) {
            $this->toastError('No puedes desactivar a otro administrador.');
            return;
        }

        $this->user->can_login = !$this->user->can_login;
        $this->user->save();
        $this->user->refresh();

        $status = $this->user->can_login ? 'activado' : 'desactivado';
        SecurityLogger::logSecurityEvent('user_account_toggled', [
            'admin_id'   => Auth::id(),
            'user_id'    => $this->user->id,
            'user_email' => $this->user->email,
            'action'     => $status,
        ]);
        $this->toastSuccess("Usuario {$status}.");
    }

    // ─── Compra uva externa ───────────────────────────────────────────────────

    public function toggleCompraUvaExterna()
    {
        if (!$this->user->isProducer()) {
            return;
        }

        $this->user->update(['compra_uva_externa' => !$this->user->compra_uva_externa]);
        $this->user->refresh();

        $estado = $this->user->compra_uva_externa ? 'activada' : 'desactivada';
        $this->toastSuccess("Compra de uva externa {$estado}.");
    }

    // ─── Impersonate ──────────────────────────────────────────────────────────

    public function impersonate()
    {
        if (!Auth::user()->isAdmin()) {
            $this->toastError('No tienes permiso para impersonar usuarios.');
            return;
        }

        if ($this->user->isAdmin()) {
            $this->toastError('No puedes impersonar a otro administrador por razones de seguridad.');
            return;
        }

        if (!$this->user->can_login) {
            $this->toastError('No puedes impersonar usuarios inactivos. Activa el usuario primero.');
            return;
        }

        session()->put('impersonating', true);
        session()->put('admin_id', Auth::id());
        session()->put('admin_name', Auth::user()->name);
        session()->put('impersonation_started_at', now()->timestamp);

        SecurityLogger::logImpersonation(Auth::id(), $this->user->id);

        Auth::login($this->user);
        session()->regenerate();

        $dashboardRoute = match($this->user->role) {
            'admin'         => 'admin.dashboard',
            'supervisor'    => 'supervisor.dashboard',
            'winery'        => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            'producer'      => 'producer.dashboard',
            default         => 'home',
        };

        $this->toastSuccess("Ahora estás viendo como: {$this->user->name}");
        return $this->redirect(route($dashboardRoute), navigate: true);
    }

    // ─── Stats ────────────────────────────────────────────────────────────────

    private function getUserStatistics(User $user): array
    {
        $stats = [
            'basic' => [
                'name'              => $user->name,
                'email'             => $user->email,
                'role'              => $user->role,
                'created_at'        => $user->created_at,
                'email_verified_at' => $user->email_verified_at,
                'can_login'         => $user->can_login,
                'is_beta_user'      => $user->is_beta_user,
                'beta_ends_at'      => $user->beta_ends_at,
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
                $stats['winery']        = $this->getWineryStats($user);
                break;
        }

        return $stats;
    }

    private function getViticulturistStats(User $user): array
    {
        return [
            'plots' => [
                'total'      => Plot::forUser($user)->count(),
                'total_area' => Plot::forUser($user)->sum('area') ?? 0,
            ],
            'clients' => [
                'total'      => Client::forUser($user->id)->count(),
                'active'     => Client::forUser($user->id)->where('active', true)->count(),
                'individual' => Client::forUser($user->id)->where('client_type', 'individual')->count(),
                'company'    => Client::forUser($user->id)->where('client_type', 'company')->count(),
            ],
            'invoices' => [
                'total'            => Invoice::forUser($user->id)->count(),
                'this_year'        => Invoice::forUser($user->id)->whereYear('invoice_date', now()->year)->count(),
                'total_amount'     => Invoice::forUser($user->id)->sum('total_amount') ?? 0,
                'this_year_amount' => Invoice::forUser($user->id)->whereYear('invoice_date', now()->year)->sum('total_amount') ?? 0,
            ],
            'activities' => [
                'total'      => AgriculturalActivity::forUser($user->id)->count(),
                'this_year'  => AgriculturalActivity::forUser($user->id)->whereYear('activity_date', now()->year)->count(),
                'this_month' => AgriculturalActivity::forUser($user->id)
                    ->whereYear('activity_date', now()->year)
                    ->whereMonth('activity_date', now()->month)
                    ->count(),
            ],
            'campaigns' => [
                'total'  => Campaign::where('viticulturist_id', $user->id)->count(),
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
        return ['note' => 'Los administradores tienen acceso completo al sistema'];
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.users.show', [
            'stats'     => $this->stats,
            'hierarchy' => $this->hierarchy,
        ])->layout('layouts.app', [
            'title'       => $this->user->name . ' - Usuario - Agro365',
            'description' => 'Detalles del usuario ' . $this->user->name . '. Información, estadísticas y actividad.',
        ]);
    }
}
