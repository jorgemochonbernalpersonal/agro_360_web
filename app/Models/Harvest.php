<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'plot_planting_id',
        'container_id',
        'harvest_start_date',
        'harvest_end_date',
        'total_weight',
        'yield_per_hectare',
        'baume_degree',
        'brix_degree',
        'acidity_level',
        'ph_level',
        'color_rating',
        'aroma_rating',
        'health_status',
        'destination_type',
        'destination',
        'transport_document_number',
        'destination_rega_code',
        'vehicle_plate',
        'buyer_name',
        'price_per_kg',
        'total_value',
        'edited_at',
        'edited_by',
        'edit_notes',
        'status',
        'notes',
    ];

    protected $casts = [
        'harvest_start_date' => 'date',
        'harvest_end_date' => 'date',
        'total_weight' => 'decimal:3',
        'yield_per_hectare' => 'decimal:3',
        'baume_degree' => 'decimal:3',
        'brix_degree' => 'decimal:3',
        'acidity_level' => 'decimal:3',
        'ph_level' => 'decimal:3',
        'price_per_kg' => 'decimal:4',
        'total_value' => 'decimal:3',
        'edited_at' => 'datetime',
    ];

    /**
     * Actividad agrícola base
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /**
     * Plantación cosechada
     */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    /**
     * Usuario que editó la cosecha
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Contenedor asignado a esta cosecha
     */
    public function container(): BelongsTo
    {
        return $this->belongsTo(HarvestContainer::class, 'container_id');
    }

    /**
     * Calcular rendimiento por hectárea y valor total automáticamente
     */
    protected static function booted()
    {
        static::saving(function ($harvest) {
            // Calcular rendimiento por hectárea
            if ($harvest->total_weight && $harvest->plotPlanting) {
                $planting = $harvest->plotPlanting;
                if ($planting->area_planted > 0) {
                    $harvest->yield_per_hectare = round($harvest->total_weight / $planting->area_planted, 3);
                }
            }

            // Calcular valor total
            if ($harvest->total_weight && $harvest->price_per_kg) {
                $harvest->total_value = round($harvest->total_weight * $harvest->price_per_kg, 3);
            }
        });
    }

    /**
     * Scope para filtrar cosechas activas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para filtrar por plantación
     */
    public function scopeForPlanting($query, int $plantingId)
    {
        return $query->where('plot_planting_id', $plantingId);
    }

    /**
     * Scope para filtrar por campaña (a través de activity)
     */
    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->whereHas('activity', function ($q) use ($campaignId) {
            $q->where('campaign_id', $campaignId);
        });
    }

    /**
     * Verificar si la cosecha fue editada
     */
    public function wasEdited(): bool
    {
        return !is_null($this->edited_at);
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Obtener el peso del contenedor asignado
     */
    public function getContainerWeight(): ?float
    {
        return $this->container ? $this->container->weight : null;
    }

    /**
     * Verificar si la cosecha tiene contenedor asignado
     */
    public function hasContainer(): bool
    {
        return !is_null($this->container_id) && $this->container !== null;
    }

    /**
     * Items de factura relacionados con esta cosecha
     */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Verificar si la cosecha está facturada
     */
    public function isInvoiced(): bool
    {
        return $this->invoiceItems()->exists();
    }

    /**
     * Movimientos de stock de esta cosecha
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(HarvestStock::class);
    }

    /**
     * Obtener el estado actual del stock
     */
    public function getCurrentStock(): array
    {
        $latest = $this->stockMovements()->latest()->first();
        
        if (!$latest) {
            return [
                'total' => 0,
                'available' => 0,
                'reserved' => 0,
                'sold' => 0,
                'gifted' => 0,
                'lost' => 0,
            ];
        }
        
        return [
            'total' => $latest->quantity_after,
            'available' => $latest->available_qty,
            'reserved' => $latest->reserved_qty,
            'sold' => $latest->sold_qty,
            'gifted' => $latest->gifted_qty,
            'lost' => $latest->lost_qty,
        ];
    }

    /**
     * Verificar si hay stock disponible
     */
    public function hasAvailableStock(float $quantity = null): bool
    {
        $stock = $this->getCurrentStock();
        
        if ($quantity === null) {
            return $stock['available'] > 0;
        }
        
        return $stock['available'] >= $quantity;
    }

    /**
     * Obtener cantidad disponible
     */
    public function getAvailableQuantity(): float
    {
        $stock = $this->getCurrentStock();
        return $stock['available'];
    }

    /**
     * Obtener cantidad reservada
     */
    public function getReservedQuantity(): float
    {
        $stock = $this->getCurrentStock();
        return $stock['reserved'];
    }

    /**
     * Obtener cantidad vendida
     */
    public function getSoldQuantity(): float
    {
        $stock = $this->getCurrentStock();
        return $stock['sold'];
    }

    /**
     * Verificar si el stock está completamente vendido
     */
    public function isFullySold(): bool
    {
        $stock = $this->getCurrentStock();
        return $stock['available'] <= 0 && $stock['reserved'] <= 0;
    }

    /**
     * Obtener porcentaje vendido
     */
    public function getSoldPercentage(): float
    {
        $stock = $this->getCurrentStock();
        
        if ($stock['total'] <= 0) {
            return 0;
        }

        return round(($stock['sold'] / $stock['total']) * 100, 2);
    }
}
