<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Concerns\WithReadOnlyGuard;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithUserAdminActions;
use App\Models\AdminNote;
use App\Models\SecurityEvent;
use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Services\SecurityLogger;
use App\Services\UserStatisticsService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Show extends Component
{
    use WithReadOnlyGuard, WithToastNotifications, WithUserAdminActions;

    public User $user;

    public $user_id;

    public $stats = [];

    public $hierarchy = [];

    public string $newNote = '';

    // Edit modal
    public $showEditModal = false;

    public $editName = '';

    public $editEmail = '';

    public $editRole = '';

    // Delete confirmation modal
    public $showDeleteModal = false;

    public $deleteCounts = [];

    public function mount($user)
    {
        if ($user instanceof User) {
            $this->user = $user;
            $this->user_id = $user->id;
        } else {
            $this->user_id = $user;
            $this->user = User::with('organization')->findOrFail($this->user_id);
        }

        $this->loadStats();
        $this->loadHierarchy();
    }

    public function loadStats()
    {
        $this->stats = app(UserStatisticsService::class)->getStatisticsForUser($this->user);
    }

    // ─── Edit ─────────────────────────────────────────────────────────────────

    public function openEditModal()
    {
        $this->editName = $this->user->name;
        $this->editEmail = $this->user->email;
        $this->editRole = $this->user->role;
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
            'editName' => 'required|string|max:255',
            'editEmail' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user->id)],
            'editRole' => 'required|in:admin,supervisor,winery,viticulturist,producer',
        ], [
            'editName.required' => __('El nombre es obligatorio.'),
            'editEmail.required' => __('El email es obligatorio.'),
            'editEmail.unique' => __('Ya existe un usuario con este email.'),
            'editRole.required' => __('El rol es obligatorio.'),
        ]);

        $emailChanged = $this->editEmail !== $this->user->email;

        $this->user->name = $this->editName;
        $this->user->email = $this->editEmail;
        $this->user->role = $this->editRole;

        if ($emailChanged) {
            $this->user->email_verified_at = null;
        }

        $this->user->save();
        $this->user->refresh();

        SecurityLogger::logSecurityEvent('user_edited_by_admin', [
            'admin_id' => Auth::id(),
            'user_id' => $this->user->id,
            'email_changed' => $emailChanged,
        ]);

        $this->loadStats();
        $this->loadHierarchy();
        $this->closeEditModal();
        $this->toastSuccess(__('Usuario actualizado correctamente.'));
    }

    // ─── Delete ───────────────────────────────────────────────────────────────

    /**
     * Abre el modal de confirmación mostrando el volumen de datos que se
     * borrarán en cascada junto al usuario.
     */
    public function confirmDeleteUser()
    {
        if ($this->isReadOnly() || ! $this->canDelete()) {
            return;
        }

        $this->deleteCounts = $this->dependencyCounts($this->user);
        $this->showDeleteModal = true;
    }

    public function deleteUser()
    {
        if ($this->isReadOnly() || ! $this->canDelete()) {
            $this->closeDeleteModal();

            return;
        }

        $name = $this->user->name;
        $email = $this->user->email;
        $id = $this->user->id;

        try {
            $this->user->delete();
        } catch (QueryException $e) {
            report($e);
            $this->toastError(__('No se pudo eliminar el usuario: tiene datos asociados que lo impiden. Considera desactivarlo en su lugar.'));
            $this->closeDeleteModal();

            return;
        }

        SecurityLogger::logSecurityEvent('user_deleted_by_admin', [
            'admin_id' => Auth::id(),
            'user_id' => $id,
            'user_email' => $email,
        ]);

        $this->toastSuccess("Usuario {$name} eliminado.");

        return $this->redirect(route('admin.users.index'), navigate: true);
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deleteCounts = [];
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
            'user_id' => $this->user->id,
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

        $this->user->can_login = ! $this->user->can_login;
        $this->user->save();
        $this->user->refresh();

        $status = $this->user->can_login ? 'activado' : 'desactivado';
        SecurityLogger::logSecurityEvent('user_account_toggled', [
            'admin_id' => Auth::id(),
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'action' => $status,
        ]);
        $this->toastSuccess("Usuario {$status}.");
    }

    // ─── Fundador ──────────────────────────────────────────────────────────────

    /**
     * Marca o desmarca al usuario como fundador.
     * Al marcarlo, extiende su beta a 1 año (beneficio del programa de fundadores).
     * Al desmarcarlo, conserva la beta actual sin recortarla.
     */
    public function toggleFounder(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        if ($this->user->isAdmin()) {
            $this->toastError(__('El programa de fundadores no aplica a administradores.'));

            return;
        }

        $makingFounder = ! $this->user->is_founder;

        $maxSlots = config('app.founder_max_slots', 25);
        if ($makingFounder && User::where('is_founder', true)->count() >= $maxSlots) {
            $this->toastError("No quedan plazas de fundador (máx. {$maxSlots}).");

            return;
        }

        $this->user->is_founder = $makingFounder;

        if ($makingFounder) {
            // Garantizar el beneficio: beta de al menos 1 año desde hoy.
            $oneYear = now()->addMonths(12)->endOfDay();
            $this->user->is_beta_user = true;
            $this->user->beta_access_granted = true;
            if (! $this->user->beta_ends_at || $this->user->beta_ends_at->lt($oneYear)) {
                $this->user->beta_ends_at = $oneYear;
            }
        }

        $this->user->save();
        $this->user->refresh();

        SecurityLogger::logSecurityEvent('user_founder_toggled', [
            'admin_id' => Auth::id(),
            'user_id' => $this->user->id,
            'user_email' => $this->user->email,
            'is_founder' => $this->user->is_founder,
        ]);

        $this->loadStats();
        $this->toastSuccess($makingFounder
            ? __('Usuario marcado como fundador (beta extendida a 1 año).')
            : __('Usuario ya no es fundador.'));
    }

    // ─── Compra uva externa ───────────────────────────────────────────────────

    public function toggleCompraUvaExterna()
    {
        if ($this->isReadOnly()) {
            return;
        }

        if (! $this->user->isProducer()) {
            return;
        }

        $this->user->update(['compra_uva_externa' => ! $this->user->compra_uva_externa]);
        $this->user->refresh();

        $estado = $this->user->compra_uva_externa ? 'activada' : 'desactivada';
        $this->toastSuccess("Compra de uva externa {$estado}.");
    }

    // ─── Impersonate ──────────────────────────────────────────────────────────

    public function impersonate()
    {
        return $this->impersonateUser($this->user);
    }

    // ─── Toggle read-only admin ───────────────────────────────────────────────

    public function toggleReadOnlyAdmin(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        if (! $this->user->isAdmin()) {
            $this->toastError(__('Solo aplica a administradores.'));

            return;
        }

        if ($this->user->id === Auth::id()) {
            $this->toastError(__('No puedes cambiarte a ti mismo a solo lectura.'));

            return;
        }

        $this->user->is_readonly_admin = ! $this->user->is_readonly_admin;
        $this->user->save();
        $this->user->refresh();

        $estado = $this->user->is_readonly_admin ? 'Solo lectura activado' : 'Acceso completo restaurado';

        SecurityLogger::logSecurityEvent('admin_readonly_toggled', [
            'admin_id' => Auth::id(),
            'user_id' => $this->user->id,
            'readonly' => $this->user->is_readonly_admin,
        ]);

        $this->toastSuccess("{$estado} para {$this->user->name}.");
    }

    // ─── Admin notes ─────────────────────────────────────────────────────────

    public function addNote(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $this->validate(['newNote' => 'required|string|min:3|max:1000'], [
            'newNote.required' => __('La nota no puede estar vacía.'),
            'newNote.min' => __('La nota debe tener al menos 3 caracteres.'),
        ]);

        if (! \Illuminate\Support\Facades\Schema::hasTable('admin_notes')) {
            $this->toastError(__('Ejecuta las migraciones pendientes primero.'));

            return;
        }
        AdminNote::create([
            'user_id' => $this->user->id,
            'admin_id' => Auth::id(),
            'note' => $this->newNote,
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
        if ($note->user_id !== $this->user->id) {
            return;
        }
        $note->delete();
        $this->toastSuccess(__('Nota eliminada.'));
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        return view('livewire.admin.users.show', [
            'stats' => $this->stats,
            'hierarchy' => $this->hierarchy,
            'userHistory' => $this->loadUserHistory(),
            'adminNotes' => \Illuminate\Support\Facades\Schema::hasTable('admin_notes')
                ? AdminNote::with('admin:id,name')->where('user_id', $this->user->id)->orderByDesc('created_at')->get()
                : collect(),
        ])->layout('layouts.app', [
            'title' => $this->user->name.' - Usuario - Agro365',
            'description' => __('Detalles del usuario ').$this->user->name.'. Información, estadísticas y actividad.',
        ]);
    }

    private function canDelete(): bool
    {
        return $this->canDeleteUser($this->user);
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
                'user_founder_toggled',
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
}
