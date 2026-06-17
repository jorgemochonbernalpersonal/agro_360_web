<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property mixed $quantity_kg
 * @property mixed $quality_rating
 * @property mixed $quality_notes
 * @property mixed $probable_alcohol
 * @property mixed $total_acidity
 * @property mixed $ph
 * @property mixed $viticulturist_id
 * @property mixed $viticulturist_name
 * @property mixed $last_reception_date
 * @property mixed $receptions
 * @property mixed $total_kg
 * @property mixed $avg_baume
 * @property mixed $avg_brix
 * @property mixed $avg_ph
 * @property mixed $avg_acidity
 * @property mixed $avg_price_per_kg
 * @property mixed $avg_price
 * @property mixed $week
 * @property mixed $count
 * @property mixed $available_qty
 * @property mixed $reserved_qty
 * @property mixed $sold_qty
 * @property mixed $available_qty_computed
 * @property mixed $pivot
 */
class Harvest extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'notebook_harvest_id',
        'winery_id',
        'batch_id',
        'plot_planting_id',
        'container_id',
        'harvest_start_date',
        'harvest_end_date',
        'vintage',
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
        // Estado sanitario detallado
        'harvest_ticket_number',
        'sanitary_state_grapes',
        'sanitary_state_agraces',
        'sanitary_state_botrytis',
        'sanitary_state_oidium',
        'sanitary_state_mildew',
        // Campos adicionales de calidad / trazabilidad
        'potential_alcohol',
        'harvest_time',
        // Descarte
        'disqualified',
        'disqualified_reason',
    ];

    protected $casts = [
        'harvest_start_date' => 'date',
        'harvest_end_date' => 'date',
        'vintage' => 'integer',
        'sanitary_state_grapes' => 'decimal:2',
        'sanitary_state_agraces' => 'decimal:2',
        'sanitary_state_botrytis' => 'decimal:2',
        'sanitary_state_oidium' => 'decimal:2',
        'sanitary_state_mildew' => 'decimal:2',
        'total_weight' => 'decimal:3',
        'yield_per_hectare' => 'decimal:3',
        'baume_degree' => 'decimal:3',
        'brix_degree' => 'decimal:3',
        'acidity_level' => 'decimal:3',
        'ph_level' => 'decimal:3',
        'price_per_kg' => 'decimal:4',
        'total_value' => 'decimal:3',
        'edited_at' => 'datetime',
        'potential_alcohol' => 'decimal:2',
        'disqualified' => 'boolean',
    ];

    /**
     * Cosecha del cuaderno de campo que originó esta recepción (solo producer, flujo "Promover").
     * Null en recepciones winery puras o en registros del cuaderno.
     */
    /** @return BelongsTo<Harvest, $this> */
    public function notebookHarvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class, 'notebook_harvest_id');
    }

    /**
     * Recepción de bodega generada a partir de esta cosecha del cuaderno (flujo "Promover").
     * Null hasta que el producer la promueva.
     */
    /** @return HasOne<Harvest, $this> */
    public function grapeReception(): HasOne
    {
        return $this->hasOne(Harvest::class, 'notebook_harvest_id');
    }

    /**
     * Actividad agrícola base (solo registros del viticultor; null en recepciones de bodega)
     */
    /** @return BelongsTo<AgriculturalActivity, $this> */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /** @return BelongsToMany<Wine, $this> */
    public function wines(): BelongsToMany
    {
        return $this->belongsToMany(Wine::class, 'wine_harvests');
    }

    /**
     * Bodega propietaria de la recepción (solo registros de bodega)
     */
    /** @return BelongsTo<User, $this> */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /**
     * Lote acumulador de recepciones de bodega
     */
    /** @return BelongsTo<GrapeReceptionBatch, $this> */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(GrapeReceptionBatch::class, 'batch_id');
    }

    /**
     * ¿Es una recepción de bodega? (vs. registro del cuaderno del viticultor)
     */
    public function isWineryReception(): bool
    {
        return $this->winery_id !== null;
    }

    /**
     * ¿Es un registro del cuaderno de campo del viticultor?
     */
    public function isViticulturistRecord(): bool
    {
        return $this->activity_id !== null;
    }

    /**
     * Plantación cosechada
     */
    /** @return BelongsTo<PlotPlanting, $this> */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    /**
     * Usuario que editó la cosecha
     */
    /** @return BelongsTo<User, $this> */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Contenedor asignado a esta cosecha
     */
    /** @return BelongsTo<Container, $this> */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'container_id');
    }

    /**
     * Scope para filtrar cosechas activas
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para filtrar por plantación
     *
     * @param mixed $query
     */
    public function scopeForPlanting($query, int $plantingId)
    {
        return $query->where('plot_planting_id', $plantingId);
    }

    /**
     * Scope para filtrar por campaña (a través de activity)
     *
     * @param mixed $query
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
        return ! is_null($this->edited_at);
    }

    /**
     * Verificar si está cancelada
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Obtener la capacidad usada del contenedor asignado
     */
    public function getContainerWeight(): ?float
    {
        return $this->container ? (float) $this->container->used_capacity : null;
    }

    /**
     * Verificar si la cosecha tiene contenedor asignado
     */
    public function hasContainer(): bool
    {
        return ! is_null($this->container_id) && $this->container !== null;
    }

    /**
     * Entrega declarada por el viticultor que se enlazó con esta recepción de bodega.
     */
    /** @return HasOne<HarvestDelivery, $this> */
    public function delivery(): HasOne
    {
        return $this->hasOne(HarvestDelivery::class, 'harvest_id');
    }

    /**
     * Items de factura relacionados con esta cosecha
     */
    /** @return HasMany<InvoiceItem, $this> */
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
    /** @return HasMany<HarvestStock, $this> */
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

        if (! $latest) {
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
    public function hasAvailableStock(?float $quantity = null): bool
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

    /**
     * Calcular rendimiento por hectárea y valor total automáticamente
     */
    protected static function booted()
    {
        static::saving(function ($harvest) {
            // Auto-set vintage from harvest_start_date if not provided
            if (! $harvest->vintage && $harvest->harvest_start_date) {
                $harvest->vintage = \Carbon\Carbon::parse($harvest->harvest_start_date)->year;
            }

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
}
