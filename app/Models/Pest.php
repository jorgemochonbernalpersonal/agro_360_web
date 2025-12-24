<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pest extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'name',
        'scientific_name',
        'description',
        'symptoms',
        'lifecycle',
        'risk_months',
        'threshold',
        'prevention_methods',
        'photos',
        'active',
    ];

    protected $casts = [
        'risk_months' => 'array',
        'photos' => 'array',
        'active' => 'boolean',
    ];

    /**
     * Productos fitosanitarios eficaces contra esta plaga
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            PhytosanitaryProduct::class,
            'pest_product_effectiveness',
            'pest_id',
            'product_id'
        )
        ->withPivot('effectiveness_rating', 'notes')
        ->withTimestamps();
    }

    /**
     * Observaciones relacionadas con esta plaga
     */
    public function observations(): HasMany
    {
        return $this->hasMany(Observation::class);
    }

    /**
     * Tratamientos fitosanitarios dirigidos a esta plaga
     */
    public function treatments(): HasMany
    {
        return $this->hasMany(PhytosanitaryTreatment::class);
    }

    /**
     * Scope: Solo plagas activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope: Filtrar por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Plagas en período de riesgo
     */
    public function scopeInRiskPeriod($query, ?int $month = null)
    {
        $month = $month ?? now()->month;
        
        return $query->whereJsonContains('risk_months', $month);
    }

    /**
     * Verificar si está en período de riesgo
     */
    public function isInRiskPeriod(?int $month = null): bool
    {
        $month = $month ?? now()->month;
        
        if (!$this->risk_months) {
            return false;
        }
        
        return in_array($month, $this->risk_months);
    }

    /**
     * Obtener productos eficaces ordenados por eficacia
     */
    public function getEffectiveProducts()
    {
        return $this->products()
            ->orderByPivot('effectiveness_rating', 'desc')
            ->get();
    }

    /**
     * Obtener nombre completo (común + científico)
     */
    public function getFullNameAttribute(): string
    {
        if ($this->scientific_name) {
            return "{$this->name} ({$this->scientific_name})";
        }
        
        return $this->name;
    }

    /**
     * Obtener icono según tipo
     */
    public function getIconAttribute(): string
    {
        return $this->type === 'pest' ? '🐛' : '🦠';
    }
}
