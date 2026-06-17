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
            && ! $this->hasActiveSubscription();
    }

    /**
     * Obtener días restantes de beta
     */
    public function betaDaysRemaining(): int
    {
        if (! $this->isBetaUser() || ! $this->beta_ends_at) {
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
     * @param \Carbon\Carbon|null $endsAt Fecha fin heredada (para cascada desde bodega).
     *                                    Si null, usa now()+3 meses.
     */
    public function isFounder(): bool
    {
        return $this->is_founder === true;
    }

    public function grantBetaAccess(?\Carbon\Carbon $endsAt = null): void
    {
        $months     = $this->isFounder() ? 12 : 3;
        $betaEndsAt = $endsAt ?? now()->addMonths($months)->endOfDay();

        $this->update([
            'is_beta_user' => true,
            'beta_ends_at' => $betaEndsAt,
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
                        'is_beta_user' => true,
                        'beta_ends_at' => $betaEndsAt,
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
        if ($this->isBetaUser() && ! $this->betaExpired()) {
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
     * Precio mensual del plan del usuario según su rol/vinculación:
     * - Productor → plan combinado viñedo+bodega (19€).
     * - Viticultor vinculado a bodega → 9€; independiente → 14€.
     */
    public function viticulturistMonthlyPrice(): float
    {
        if ($this->isProducer()) {
            return \App\Models\Subscription::PRICE_MONTHLY_PRODUCER;
        }

        return $this->hasWinery()
            ? \App\Models\Subscription::PRICE_MONTHLY_VIT_LINKED
            : \App\Models\Subscription::PRICE_MONTHLY_INDEPENDENT;
    }

    /**
     * Precio anual del plan del usuario según su rol/vinculación.
     */
    public function viticulturistYearlyPrice(): float
    {
        if ($this->isProducer()) {
            return \App\Models\Subscription::PRICE_YEARLY_PRODUCER;
        }

        return $this->hasWinery()
            ? \App\Models\Subscription::PRICE_YEARLY_VIT_LINKED
            : \App\Models\Subscription::PRICE_YEARLY_INDEPENDENT;
    }

    /**
     * Plan efectivo del usuario según rol + estado de acceso.
     *
     * Sin acceso activo (beta/suscripción) → 'free'.
     * Con acceso activo → plan correspondiente al rol.
     *
     * @return 'free'|'vit_pro'|'winery'|'do'
     */
    public function effectivePlan(): string
    {
        if (! $this->hasActiveAccess()) {
            return 'free';
        }

        if ($this->isSupervisor()) {
            return 'do';
        }

        if ($this->hasWineryAccess() && ! $this->hasViticulturistAccess()) {
            return 'winery';
        }

        return 'vit_pro';
    }

    /**
     * Conjunto de ability codes que le corresponden al usuario según su plan efectivo.
     * Resuelve los prefijos '@plan' de config/plans.php.
     *
     * @return array<string>
     */
    public function planAbilities(): array
    {
        $plans = config('plans', []);

        $resolve = function (string $plan) use ($plans, &$resolve): array {
            $codes = $plans[$plan] ?? [];
            $result = [];

            foreach ($codes as $code) {
                if (str_starts_with($code, '@')) {
                    $result = array_merge($result, $resolve(substr($code, 1)));
                } else {
                    $result[] = $code;
                }
            }

            return array_unique($result);
        };

        return $resolve($this->effectivePlan());
    }

    /**
     * Verificar si el usuario tiene una ability según su plan efectivo O
     * si la DO le ha concedido un override individual (lógica existente en hasAbility).
     * Este método unifica ambas fuentes para que el middleware pueda usarlo.
     */
    public function hasPlanAbility(string $code): bool
    {
        return in_array($code, $this->planAbilities(), true);
    }

    // ── Tramos DO ─────────────────────────────────────────────────────────

    /**
     * Número de bodegas adscritas a este supervisor/DO.
     * Para roles no-supervisor siempre devuelve 0.
     */
    public function managedWineryCount(): int
    {
        if (! $this->isSupervisor()) {
            return 0;
        }

        return \Illuminate\Support\Facades\DB::table('supervisor_winery')
            ->where('supervisor_id', $this->id)
            ->count();
    }

    /**
     * Tramo DO que le corresponde según bodegas adscritas.
     *
     * @return array{min:int,max:int|null,label:string,monthly:float|null,yearly:float|null}
     */
    public function doTier(): array
    {
        return \App\Models\Subscription::doTierForCount(
            $this->managedWineryCount()
        );
    }

    /**
     * Precio mensual DO según tramo. Null = enterprise (requiere negociación).
     */
    public function doMonthlyPrice(): ?float
    {
        return $this->doTier()['monthly'];
    }

    /**
     * Precio anual DO según tramo. Null = enterprise.
     */
    public function doYearlyPrice(): ?float
    {
        return $this->doTier()['yearly'];
    }

    // ── Tramos de red bodega ───────────────────────────────────────────────

    /**
     * Número de viticultores activos vinculados a esta bodega.
     * Para roles no-bodega siempre devuelve 0.
     */
    public function managedViticulturistCount(): int
    {
        if (! $this->hasWineryAccess()) {
            return 0;
        }

        return \Illuminate\Support\Facades\DB::table('winery_viticulturist')
            ->where('winery_id', $this->id)
            ->count();
    }

    /**
     * Tramo de red que le corresponde a esta bodega según viticultores gestionados.
     *
     * @return array{min:int,max:int|null,label:string,monthly:float|null,yearly:float|null}
     */
    public function wineryTier(): array
    {
        return \App\Models\Subscription::wineryTierForCount(
            $this->managedViticulturistCount()
        );
    }

    /**
     * Precio mensual que le corresponde a la bodega según su tramo de red.
     * Devuelve null si el tramo es enterprise (requiere negociación).
     */
    public function wineryMonthlyPrice(): ?float
    {
        return $this->wineryTier()['monthly'];
    }

    /**
     * Precio anual que le corresponde a la bodega según su tramo de red.
     * Devuelve null si el tramo es enterprise (requiere negociación).
     */
    public function wineryYearlyPrice(): ?float
    {
        return $this->wineryTier()['yearly'];
    }
}
