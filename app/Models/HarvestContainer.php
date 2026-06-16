<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HarvestContainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'harvest_id',
        'container_type',
        'container_number',
        'quantity',
        'weight',
        'weight_per_unit',
        'location',
        'status',
        'filled_date',
        'delivery_date',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:3',
        'weight_per_unit' => 'decimal:3',
        'filled_date' => 'date',
        'delivery_date' => 'date',
    ];

    /**
     * Cosecha a la que pertenece este contenedor (opcional)
     */
    /** @return BelongsTo<Harvest, $this> */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Cosechas que usan este contenedor (relación inversa)
     */
    /** @return HasMany<Harvest, $this> */
    public function harvests(): HasMany
    {
        return $this->hasMany(Harvest::class, 'container_id');
    }

    /**
     * Scope para filtrar por tipo de contenedor
     *
     * @param mixed $query
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('container_type', $type);
    }

    /**
     * Scope para filtrar por estado
     *
     * @param mixed $query
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para contenedores entregados
     *
     * @param mixed $query
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope para contenedores en almacén
     *
     * @param mixed $query
     */
    public function scopeStored($query)
    {
        return $query->where('status', 'stored');
    }

    /**
     * Scope para contenedores disponibles (sin cosecha asignada)
     *
     * @param mixed $query
     */
    public function scopeAvailable($query)
    {
        return $query->whereNull('harvest_id');
    }

    /**
     * Scope para contenedores asignados (con cosecha)
     *
     * @param mixed $query
     */
    public function scopeAssigned($query)
    {
        return $query->whereNotNull('harvest_id');
    }

    /**
     * Verificar si el contenedor está entregado
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Verificar si el contenedor está vacío
     */
    public function isEmpty(): bool
    {
        return $this->status === 'empty';
    }

    /**
     * Verificar si el contenedor está disponible (sin cosecha asignada)
     */
    public function isAvailable(): bool
    {
        return is_null($this->harvest_id);
    }

    /**
     * Verificar si el contenedor está asignado a una cosecha
     */
    public function isAssigned(): bool
    {
        return ! is_null($this->harvest_id);
    }

    /**
     * Calcular peso por unidad automáticamente
     */
    protected static function booted()
    {
        static::saving(function ($container) {
            if ($container->weight && $container->quantity && $container->quantity > 0) {
                $container->weight_per_unit = round($container->weight / $container->quantity, 3);
            }
        });
    }
}
