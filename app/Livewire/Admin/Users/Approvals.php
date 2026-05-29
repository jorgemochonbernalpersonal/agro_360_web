<?php

namespace App\Livewire\Admin\Users;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Approvals extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search = '';

    protected $queryString = ['search' => ['except' => '']];

    public function updatingSearch(): void { $this->resetPage(); }

    public function approve(int $userId): void
    {
        $user = User::findOrFail($userId);
        $user->can_login = true;
        $user->save();

        SecurityLogger::logSecurityEvent('user_approved_by_admin', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'user_email' => $user->email,
        ]);

        $this->toastSuccess("{$user->name} aprobado — puede iniciar sesión.");
    }

    public function reject(int $userId): void
    {
        $user = User::findOrFail($userId);
        $name = $user->name;

        SecurityLogger::logSecurityEvent('user_rejected_by_admin', [
            'admin_id'   => Auth::id(),
            'user_id'    => $user->id,
            'user_email' => $user->email,
        ]);

        $user->delete();
        $this->toastSuccess("Cuenta de \"{$name}\" eliminada.");
    }

    public function render()
    {
        // Pending = verified email + can_login = false + not admin + not demo + created recently
        $query = User::query()
            ->where('can_login', false)
            ->whereNotNull('email_verified_at')
            ->where('role', '!=', 'admin')
            ->orderBy('email_verified_at');

        if ($this->search) {
            $s = '%' . $this->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $s)->orWhere('email', 'like', $s));
        }

        $pending = $query->paginate(20);
        $total   = User::query()->where('can_login', false)->whereNotNull('email_verified_at')->where('role', '!=', 'admin')->count();

        return view('livewire.admin.users.approvals', compact('pending', 'total'))
            ->layout('layouts.app', [
                'title'       => __('Aprobaciones - Admin - Agro365'),
                'description' => __('Usuarios verificados pendientes de activación manual'),
            ]);
    }
}
