<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingCertification extends Model
{
    protected $fillable = [
        'plot_planting_id',
        'type',
        'certification_number',
        'certifying_body',
        'certification_date',
        'expiry_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'certification_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Plantación certificada
     */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class);
    }

    /**
     * Verificar si está próxima a vencer (30 días)
     */
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date || $this->status !== 'active') {
            return false;
        }
        
        return $this->expiry_date->diffInDays(now()) <= 30 
            && $this->expiry_date->isFuture();
    }

    /**
     * Verificar si está vencida
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return $this->expiry_date->isPast();
    }

    /**
     * Scope: Certificaciones activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Certificaciones próximas a vencer
     */
    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>', now())
            ->whereDate('expiry_date', '<=', now()->addDays(30));
    }

    /**
     * Scope: Por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
