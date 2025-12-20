<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'user_id',
        'client_type',
        'first_name',
        'last_name',
        'email',
        'phone',
        'company_name',
        'company_document',
        'particular_document',
        'default_discount',
        'payment_method',
        'account_number',
        'has_cae',
        'cae_number',
        'active',
        'balance',
        'avatar',
        'notes',
    ];

    protected $casts = [
        'default_discount' => 'decimal:2',
        'balance' => 'decimal:2',
        'has_cae' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Usuario (viticultor) propietario del cliente
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Direcciones del cliente
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(ClientAddress::class);
    }

    /**
     * Dirección por defecto
     */
    public function defaultAddress()
    {
        return $this->hasOne(ClientAddress::class)->where('is_default', true);
    }

    /**
     * Facturas del cliente
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Obtener nombre completo
     */
    public function getFullNameAttribute(): string
    {
        if ($this->client_type === 'company') {
            return $this->company_name ?? '';
        }
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    /**
     * Verificar si es empresa
     */
    public function isCompany(): bool
    {
        return $this->client_type === 'company';
    }

    /**
     * Verificar si es particular
     */
    public function isIndividual(): bool
    {
        return $this->client_type === 'individual';
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para clientes de un usuario
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
