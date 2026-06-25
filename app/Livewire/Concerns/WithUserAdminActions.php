<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Services\SecurityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait WithUserAdminActions
{
    /**
     * Guard: returns false and shows a toast if the user cannot be deleted.
     */
    protected function canDeleteUser(User $user): bool
    {
        if ($user->isAdmin()) {
            $this->toastError(__('No puedes eliminar a un administrador.'));

            return false;
        }

        if ($user->id === Auth::id()) {
            $this->toastError(__('No puedes eliminarte a ti mismo.'));

            return false;
        }

        return true;
    }

    /**
     * Counts of cascade-deleted data for a user. Only non-zero entries returned.
     *
     * @return array<string, int>
     */
    protected function dependencyCounts(User $user): array
    {
        return array_filter([
            __('Campañas') => $user->campaigns()->count(),
            __('Actividades agrícolas') => $user->agriculturalActivities()->count(),
            __('Parcelas') => $user->plots()->count(),
            __('Clientes') => DB::table('clients')->where('user_id', $user->id)->count(),
            __('Facturas') => DB::table('invoices')->where('user_id', $user->id)->count(),
        ], fn ($count) => $count > 0);
    }

    /**
     * Core impersonation logic. Each component's public impersonate() delegates here.
     */
    protected function impersonateUser(User $targetUser)
    {
        if (! Auth::user()->isAdmin()) {
            $this->toastError(__('No tienes permiso para impersonar usuarios.'));

            return;
        }

        if ($targetUser->isAdmin()) {
            $this->toastError(__('No puedes impersonar a otro administrador por razones de seguridad.'));

            return;
        }

        if (! $targetUser->can_login) {
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

        $dashboardRoute = match ($targetUser->role) {
            'admin' => 'admin.dashboard',
            'supervisor' => 'supervisor.dashboard',
            'winery' => 'winery.dashboard',
            'viticulturist' => 'viticulturist.dashboard',
            'producer' => 'producer.dashboard',
            default => 'home',
        };

        $this->toastSuccess("Ahora estás viendo como: {$targetUser->name}");

        return $this->redirect(route($dashboardRoute), navigate: true);
    }
}
