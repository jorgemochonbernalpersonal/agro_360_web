<?php

namespace App\Models\Traits;

trait HasBetaAccess
{
    /**
     * Verificar si el usuario es usuario beta
     */
    public function isBetaUser(): bool
    {
        return $this->is_beta_user === true;
    }

    /**
     * Verificar si la beta ha expirado
     */
    public function betaExpired(): bool
    {
        return $this->is_beta_user
            && $this->beta_ends_at
            && $this->beta_ends_at->isPast()
            && !$this->hasActiveSubscription();
    }

    /**
     * Obtener días restantes de beta
     */
    public function betaDaysRemaining(): int
    {
        if (!$this->isBetaUser() || !$this->beta_ends_at) {
            return 0;
        }

        if ($this->beta_ends_at->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($this->beta_ends_at, false);
    }

    /**
     * Activar acceso beta (hasta 30/06/2026).
     * Si el usuario es una bodega, cascada beta a sus viticultores vinculados.
     */
    public function grantBetaAccess(): void
    {
        $this->update([
            'is_beta_user'        => true,
            'beta_ends_at'        => \Carbon\Carbon::parse('2026-06-30 23:59:59'),
            'beta_access_granted' => true,
        ]);

        if ($this->role === 'winery') {
            $viticulturistIds = \Illuminate\Support\Facades\DB::table('winery_viticulturist')
                ->where('winery_id', $this->id)
                ->pluck('viticulturist_id');

            if ($viticulturistIds->isNotEmpty()) {
                \App\Models\User::whereIn('id', $viticulturistIds)
                    ->where('is_beta_user', false)
                    ->update([
                        'is_beta_user'        => true,
                        'beta_ends_at'        => \Carbon\Carbon::parse('2026-06-30 23:59:59'),
                        'beta_access_granted' => true,
                    ]);
            }
        }
    }

    /**
     * Verificar si tiene acceso activo (beta o suscripción)
     */
    public function hasActiveAccess(): bool
    {
        // Beta activo
        if ($this->isBetaUser() && !$this->betaExpired()) {
            return true;
        }

        // Suscripción activa
        if ($this->hasActiveSubscription()) {
            return true;
        }

        return false;
    }
}
