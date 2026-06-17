<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property mixed $total
 * @property mixed $active
 * @property mixed $month
 * @property mixed $year
 * @property mixed $cancelled
 * @property mixed $expired
 * @property mixed $revenue
 */
class Subscription extends Model
{
    use HasFactory;

    // Constantes para tipos de plan
    public const PLAN_MONTHLY = 'monthly';

    public const PLAN_YEARLY = 'yearly';

    // Constantes para estados
    public const STATUS_ACTIVE = 'active';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_EXPIRED = 'expired';

    // Viticultor invitado por bodega (Capa Completa)
    public const PRICE_MONTHLY_VIT_LINKED = 9.00;

    public const PRICE_YEARLY_VIT_LINKED = 85.00;

    // Viticultor independiente (sin bodega)
    public const PRICE_MONTHLY_INDEPENDENT = 14.00;

    public const PRICE_YEARLY_INDEPENDENT = 130.00;

    // Productor (viñedo + bodega propios en una cuenta)
    public const PRICE_MONTHLY_PRODUCER = 19.00;

    public const PRICE_YEARLY_PRODUCER = 180.00;

    // Tramos bodega según nº de viticultores gestionados (Capa red).
    // Cada tramo: min, max (null = sin límite), label, monthly, yearly.
    // monthly/yearly null = enterprise (requiere negociación).
    public const WINERY_TIERS = [
        ['min' => 0,   'max' => 0,    'label' => 'solo_vino',  'monthly' => 19.00,  'yearly' => 180.00],
        ['min' => 1,   'max' => 10,   'label' => 'red_s',      'monthly' => 29.00,  'yearly' => 290.00],
        ['min' => 11,  'max' => 30,   'label' => 'red_m',      'monthly' => 49.00,  'yearly' => 490.00],
        ['min' => 31,  'max' => 60,   'label' => 'red_l',      'monthly' => 79.00,  'yearly' => 790.00],
        ['min' => 61,  'max' => 100,  'label' => 'red_xl',     'monthly' => 119.00, 'yearly' => 1190.00],
        ['min' => 101, 'max' => null, 'label' => 'enterprise', 'monthly' => null,   'yearly' => null],
    ];

    // Tramos DO según nº de bodegas adscritas.
    public const DO_TIERS = [
        ['min' => 0,   'max' => 25,  'label' => 'do_s',      'monthly' => 149.00, 'yearly' => 1400.00],
        ['min' => 26,  'max' => 50,  'label' => 'do_m',      'monthly' => 249.00, 'yearly' => 2350.00],
        ['min' => 51,  'max' => 75,  'label' => 'do_l',      'monthly' => 349.00, 'yearly' => 3300.00],
        ['min' => 76,  'max' => 100, 'label' => 'do_xl',     'monthly' => 449.00, 'yearly' => 4250.00],
        ['min' => 101, 'max' => null,'label' => 'enterprise', 'monthly' => null,   'yearly' => null],
    ];

    /**
     * Resuelve el tramo DO según el número de bodegas adscritas.
     *
     * @return array{min:int,max:int|null,label:string,monthly:float|null,yearly:float|null}
     */
    public static function doTierForCount(int $count): array
    {
        foreach (array_reverse(self::DO_TIERS) as $tier) {
            if ($count >= $tier['min']) {
                return $tier;
            }
        }

        return self::DO_TIERS[0];
    }

    /**
     * Resuelve el tramo de bodega según el número de viticultores gestionados.
     *
     * @return array{min:int,max:int|null,label:string,monthly:float|null,yearly:float|null}
     */
    public static function wineryTierForCount(int $count): array
    {
        foreach (array_reverse(self::WINERY_TIERS) as $tier) {
            if ($count >= $tier['min']) {
                return $tier;
            }
        }

        return self::WINERY_TIERS[0];
    }

    protected $fillable = [
        'user_id',
        'plan_type',
        'amount',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'paypal_subscription_id',
        'paypal_plan_id',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Relación con el usuario
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los pagos
     */
    /** @return HasMany<Payment, $this> */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verificar si la suscripción está activa
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at->isFuture();
    }

    /**
     * Verificar si la suscripción está expirada
     */
    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    /**
     * Cancelar suscripción
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
