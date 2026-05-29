<?php

namespace App\Livewire\Admin\Users;

use App\Models\AdminNote;
use App\Models\User;
use App\Models\Plot;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Crew;
use App\Models\Wine;
use App\Models\Container;
use App\Models\SecurityEvent;
use App\Models\SupportTicket;
use App\Models\SupervisorWinery;
use App\Models\SupervisorViticulturist;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\DB;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications, WithReadOnlyGuard;

    public User $user;
    public $user_id;
    public $stats     = [];
    public $hierarchy = [];
    public string $newNote = '';

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
        if ($this->isReadOnly()) {
            return;
        }

        $this->validate([
            'editName'  => 'required|string|max:255',
            'editEmail' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'editRole'  => 'required|in:admin,supervisor,winery,viticulturist,producer',
        ], [
            'editName.required'  => __('El nombre es obligatorio.'),
            'editEmail.required' => __('El email es obligatorio.'),
            'editEmail.unique'   => __('Ya existe un usuario con este email.'),
            'editRole.required'  => __('El rol es obligatorio.'),
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
        $this->toastSuccess(__('Usuario actualizado correctamente.'));
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    public function deleteUser()
    {
        if ($this->isReadOnly()) {
            return;
        }

        if ($this->user->isAdmin()) {
            $this->toastError(__('No puedes eliminar a un administrador.'));
            return;
        }

        if ($this->user->id === Auth::id()) {
            $this->toastError(__('No puedes eliminarte a ti mismo.'));
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
        if ($this->isReadOnly()) {
            return;
        }

        $status = Password::sendResetLink(['email' => $this->user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->toastSuccess(__('Email de restablecimiento enviado a :email.', ['email' => $this->user->email]));
        } else {
            $this->toastError(__('No se pudo enviar el email. Verifica que el email existe y es válido.'));
        }
    }

    // ─── Verify email manually ────────────────────────────────────────────────

    public function verifyEmailManually()
    {
        if ($this->isReadOnly()) {
            return;
        }

        if ($this->user->email_verified_at) {
            $this->toastError(__('El email ya está verificado.'));
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
        $this->toastSuccess(__('Email verificado manualmente.'));
    }

    // ─── Toggle active ────────────────────────────────────────────────────────

    public function toggleActive()
    {
        if ($this->isReadOnly()) {
            return;
        }

        if ($this->user->isAdmin() && $this->user->id !== Auth::id()) {
            $this->toastError(__('No puedes desactivar a otro administrador.'));
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
        if ($this->isReadOnly()) {
            return;
        }

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
            $this->toastError(__('No tienes permiso para impersonar usuarios.'));
            return;
        }

        if ($this->user->isAdmin()) {
            $this->toastError(__('No puedes impersonar a otro administrador por razones de seguridad.'));
            return;
        }

        if (!$this->user->can_login) {
            $this->toastError(__('No puedes impersonar usuarios inactivos. Activa el usuario primero.'));
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

    // ─── Toggle read-only admin ───────────────────────────────────────────────

    public function toggleReadOnlyAdmin(): void
    {
        if ($this->isReadOnly()) return;

        if (!$this->user->isAdmin()) {
            $this->toastError(__('Solo aplica a administradores.'));
            return;
        }

        if ($this->user->id === Auth::id()) {
            $this->toastError(__('No puedes cambiarte a ti mismo a solo lectura.'));
            return;
        }

        $this->user->is_readonly_admin = !$this->user->is_readonly_admin;
        $this->user->save();
        $this->user->refresh();

        $estado = $this->user->is_readonly_admin ? 'Solo lectura activado' : 'Acceso completo restaurado';

        SecurityLogger::logSecurityEvent('admin_readonly_toggled', [
            'admin_id'    => Auth::id(),
            'user_id'     => $this->user->id,
            'readonly'    => $this->user->is_readonly_admin,
        ]);

        $this->toastSuccess("{$estado} para {$this->user->name}.");
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
        $uid  = $user->id;
        $year = now()->year;

        $invoiceRaw = DB::table('invoices')
            ->where('user_id', $uid)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN YEAR(invoice_date) = {$year} THEN 1 ELSE 0 END) as this_year")
            ->selectRaw("COALESCE(SUM(total_amount), 0) as total_amount")
            ->selectRaw("COALESCE(SUM(CASE WHEN YEAR(invoice_date) = {$year} THEN total_amount ELSE 0 END), 0) as this_year_amount")
            ->selectRaw("SUM(CASE WHEN payment_status = 'unpaid' AND status != 'cancelled' THEN 1 ELSE 0 END) as pending")
            ->first();

        $wineRaw = DB::table('wines')
            ->where('user_id', $uid)
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress")
            ->selectRaw("SUM(CASE WHEN status = 'bottled' THEN 1 ELSE 0 END) as bottled")
            ->selectRaw("COALESCE(SUM(volume_liters), 0) as total_liters")
            ->first();

        return [
            'viticulturists' => [
                'total' => WineryViticulturist::where('winery_id', $uid)->count(),
            ],
            'crews' => [
                'total' => Crew::where('winery_id', $uid)->count(),
            ],
            'clients' => [
                'total'  => Client::forUser($uid)->count(),
                'active' => Client::forUser($uid)->where('active', true)->count(),
            ],
            'invoices' => [
                'total'            => (int) $invoiceRaw->total,
                'this_year'        => (int) $invoiceRaw->this_year,
                'total_amount'     => (float) $invoiceRaw->total_amount,
                'this_year_amount' => (float) $invoiceRaw->this_year_amount,
                'pending'          => (int) $invoiceRaw->pending,
            ],
            'wines' => [
                'total'       => (int) $wineRaw->total,
                'in_progress' => (int) $wineRaw->in_progress,
                'bottled'     => (int) $wineRaw->bottled,
                'total_liters'=> (float) $wineRaw->total_liters,
            ],
            'containers' => [
                'total'    => Container::where('user_id', $uid)->count(),
                'active'   => Container::where('user_id', $uid)->where('archived', false)->count(),
            ],
        ];
    }

    private function getSupervisorStats(User $user): array
    {
        $wineryIds = SupervisorWinery::where('supervisor_id', $user->id)->pluck('winery_id');
        $vitIds    = SupervisorViticulturist::where('supervisor_id', $user->id)->pluck('viticulturist_id');

        return [
            'wineries' => [
                'total'    => $wineryIds->count(),
                'active'   => User::whereIn('id', $wineryIds)->where('can_login', true)->count(),
                'inactive' => User::whereIn('id', $wineryIds)->where('can_login', false)->count(),
            ],
            'viticulturists' => [
                'total'    => $vitIds->count(),
                'active'   => User::whereIn('id', $vitIds)->where('can_login', true)->count(),
                'inactive' => User::whereIn('id', $vitIds)->where('can_login', false)->count(),
            ],
        ];
    }

    private function getAdminStats(User $user): array
    {
        $now   = now();
        $year  = $now->year;
        $month = $now->month;

        $userRaw = DB::table('users')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN can_login = 1 THEN 1 ELSE 0 END) as active")
            ->selectRaw("SUM(CASE WHEN MONTH(created_at) = {$month} AND YEAR(created_at) = {$year} THEN 1 ELSE 0 END) as new_this_month")
            ->first();

        $ticketRaw = DB::table('support_tickets')
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN status IN ('open','in_progress') THEN 1 ELSE 0 END) as open")
            ->selectRaw("SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as new_this_week", [$now->copy()->subWeek()])
            ->first();

        return [
            'users' => [
                'total'          => (int) $userRaw->total,
                'active'         => (int) $userRaw->active,
                'new_this_month' => (int) $userRaw->new_this_month,
            ],
            'plots' => [
                'total'      => (int) DB::table('plots')->count(),
                'total_area' => (float) DB::table('plots')->sum('area'),
            ],
            'support' => [
                'total'        => (int) $ticketRaw->total,
                'open'         => (int) $ticketRaw->open,
                'new_this_week'=> (int) $ticketRaw->new_this_week,
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

    // ─── Admin notes ─────────────────────────────────────────────────────────

    public function addNote(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $this->validate(['newNote' => 'required|string|min:3|max:1000'], [
            'newNote.required' => __('La nota no puede estar vacía.'),
            'newNote.min'      => __('La nota debe tener al menos 3 caracteres.'),
        ]);

        if (!\Illuminate\Support\Facades\Schema::hasTable('admin_notes')) {
            $this->toastError(__('Ejecuta las migraciones pendientes primero.'));
            return;
        }
        AdminNote::create([
            'user_id'  => $this->user->id,
            'admin_id' => Auth::id(),
            'note'     => $this->newNote,
        ]);

        $this->newNote = '';
        $this->toastSuccess(__('Nota añadida.'));
    }

    public function deleteNote(int $id): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $note = AdminNote::findOrFail($id);
        if ($note->user_id !== $this->user->id) return;
        $note->delete();
        $this->toastSuccess(__('Nota eliminada.'));
    }

    // ─── User history ─────────────────────────────────────────────────────────

    private function loadUserHistory(): \Illuminate\Support\Collection
    {
        $userId = $this->user->id;

        return SecurityEvent::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('admin_id', $userId);
            })
            ->whereIn('event', [
                'user_created_by_admin',
                'user_edited_by_admin',
                'user_deleted_by_admin',
                'user_account_toggled',
                'user_beta_toggled',
                'email_verified_manually_by_admin',
                'impersonation_started',
                'admin_readonly_toggled',
                'login',
                'logout',
                'failed_login',
                'password_reset_requested',
                'password_changed',
            ])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.users.show', [
            'stats'       => $this->stats,
            'hierarchy'   => $this->hierarchy,
            'userHistory' => $this->loadUserHistory(),
            'adminNotes'  => \Illuminate\Support\Facades\Schema::hasTable('admin_notes')
                ? AdminNote::with('admin:id,name')->where('user_id', $this->user->id)->orderByDesc('created_at')->get()
                : collect(),
        ])->layout('layouts.app', [
            'title'       => $this->user->name . ' - Usuario - Agro365',
            'description' => __('Detalles del usuario ') . $this->user->name . '. Información, estadísticas y actividad.',
        ]);
    }
}
