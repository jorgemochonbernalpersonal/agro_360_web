<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;

trait WithReadOnlyGuard
{
    /**
     * Returns true and emits an error toast if the current admin is read-only.
     * Usage: if ($this->isReadOnly()) return;
     */
    protected function isReadOnly(): bool
    {
        if (Auth::check() && Auth::user()->isAdmin() && Auth::user()->is_readonly_admin) {
            $this->toastError('Tu cuenta es de solo lectura. Contacta con el superadmin para realizar cambios.');
            return true;
        }

        return false;
    }
}
