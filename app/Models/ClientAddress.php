<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientAddress extends Model
{
    protected $fillable = [
        'client_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'position',
        'address',
        'autonomous_community_id',
        'province_id',
        'municipality_id',
        'postal_code',
        'is_default',
        'description',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Cliente al que pertenece esta dirección
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Comunidad autónoma
     */
    public function autonomousCommunity(): BelongsTo
    {
        return $this->belongsTo(AutonomousCommunity::class);
    }

    /**
     * Provincia
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Municipio
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Facturas que usan esta dirección
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'client_address_id');
    }

    /**
     * Obtener dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        $parts = [];
        
        if ($this->address) {
            $parts[] = $this->address;
        }
        
        if ($this->municipality) {
            $parts[] = $this->municipality->name;
        }
        
        if ($this->province) {
            $parts[] = $this->province->name;
        }
        
        if ($this->postal_code) {
            $parts[] = $this->postal_code;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Scope para direcciones por defecto
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
