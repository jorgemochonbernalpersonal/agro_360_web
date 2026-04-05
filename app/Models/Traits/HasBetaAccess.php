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
     * Activar acceso beta.
     * Si el usuario es una bodega, cascada beta a sus viticultores vinculados.
     *
     * @param  \Carbon\Carbon|null  $endsAt  Fecha fin heredada (para cascada desde bodega).
     *                                        Si null, usa now()+3 meses.
     */
    public function grantBetaAccess(?\Carbon\Carbon $endsAt = null): void
    {
        $betaEndsAt = $endsAt ?? now()->addMonths(3)->endOfDay();

        $this->update([
            'is_beta_user'        => true,
            'beta_ends_at'        => $betaEndsAt,
            'beta_access_granted' => true,
        ]);

        if ($this->hasWineryAccess()) {
            $viticulturistIds = \Illuminate\Support\Facades\DB::table('winery_viticulturist')
                ->where('winery_id', $this->id)
                ->pluck('viticulturist_id');

            if ($viticulturistIds->isNotEmpty()) {
                \App\Models\User::whereIn('id', $viticulturistIds)
                    ->where('is_beta_user', false)
                    ->update([
                        'is_beta_user'        => true,
                        'beta_ends_at'        => $betaEndsAt,
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

    /**
     * Viticultor → acceso básico gratis permanente.
     * Vinculado a bodega o independiente: cuaderno, parcelas, SIGPAC, fenología, etc.
     */
    public function hasBasicFreeAccess(): bool
    {
        return $this->hasViticulturistAccess();
    }

    /**
     * Viticultor vinculado a una bodega (usado para determinar pricing).
     */
    public function isWineryLinkedViticulturist(): bool
    {
        return $this->isViticulturist() && $this->hasWinery();
    }

    /**
     * Precio mensual según si está vinculado a bodega o es independiente
     */
    public function viticulturistMonthlyPrice(): float
    {
        return $this->hasWinery()
            ? \App\Models\Subscription::PRICE_MONTHLY_WINERY
            : \App\Models\Subscription::PRICE_MONTHLY_INDEPENDENT;
    }

    /**
     * Precio anual según si está vinculado a bodega o es independiente
     */
    public function viticulturistYearlyPrice(): float
    {
        return $this->hasWinery()
            ? \App\Models\Subscription::PRICE_YEARLY_WINERY
            : \App\Models\Subscription::PRICE_YEARLY_INDEPENDENT;
    }
}
