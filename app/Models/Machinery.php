<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machinery extends Model
{
    use HasFactory;
    
    protected $table = 'machinery';
    
    protected $fillable = [
        'name',
        'type',
        'brand',
        'model',
        'serial_number',
        'year',
        'purchase_date',
        'purchase_price',
        'current_value',
        'roma_registration',
        'is_rented',
        'capacity',
        'last_revision_date',
        'image',
        'notes',
        'viticulturist_id',
        'active',
    ];

    protected $casts = [
        'year' => 'integer',
        'purchase_date' => 'date',
        'last_revision_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'is_rented' => 'boolean',
        'active' => 'boolean',
    ];

    /**
     * Viticultor propietario de la maquinaria
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Actividades agrícolas donde se usó esta maquinaria
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'machinery_id');
    }

    /**
     * Scope para filtrar por viticultor
     */
    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Scope para filtrar maquinaria activa
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por tipo
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtener el número de actividades
     */
    public function getActivitiesCountAttribute(): int
    {
        return $this->activities()->count();
    }
}
