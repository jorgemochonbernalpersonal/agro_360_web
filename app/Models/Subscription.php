<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;
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

    // Constantes para tipos de plan
    public const PLAN_MONTHLY = 'monthly';
    public const PLAN_YEARLY = 'yearly';

    // Constantes para estados
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    // Precios para viticultor invitado por bodega
    public const PRICE_MONTHLY_WINERY = 9.00;
    public const PRICE_YEARLY_WINERY  = 85.00;

    // Precios para viticultor independiente (sin bodega)
    public const PRICE_MONTHLY_INDEPENDENT = 14.00;
    public const PRICE_YEARLY_INDEPENDENT  = 130.00;

    // Alias legacy (precio anterior, por compatibilidad)
    public const PRICE_MONTHLY = 12.00;
    public const PRICE_YEARLY  = 120.00;

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con los pagos
     */
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
