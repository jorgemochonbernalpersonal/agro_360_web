<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property-read mixed $current_phase_label
 * @property-read mixed $current_phase_type
 * @property-read mixed $days_total
 * @property-read mixed $days_in_phase
 * @property-read mixed $vintage_year
 * @property-read mixed $total
 * @property-read mixed $in_progress
 * @property-read mixed $bottled
 * @property-read mixed $sold
 * @property-read mixed $aged
 */
class Wine extends Model
{
    const WINE_TYPES = [
        'red' => 'Tinto',
        'white' => 'Blanco',
        'rose' => 'Rosado',
        'sparkling' => 'Espumoso',
        'fortified' => 'Generoso / Fortificado',
        'sweet' => 'Dulce',
        'semi_sweet' => 'Semidulce',
        'other' => 'Otro',
    ];

    const STATUSES = [
        'in_progress' => 'En elaboración',
        'aged' => 'En crianza',
        'bottled' => 'Embotellado',
        'sold' => 'Vendido',
        'cancelled' => 'Cancelado',
    ];

    const AGING_TYPES = [
        'joven' => 'Joven',
        'roble' => 'Roble',
        'crianza' => 'Crianza',
        'reserva' => 'Reserva',
        'gran_reserva' => 'Gran Reserva',
        'other' => 'Otro',
    ];

    const CATEGORIES = [
        'VdM' => 'Vino de Mesa',
        'IGP' => 'IGP',
        'DO' => 'DO',
        'DOCa' => 'DOCa',
        'vino_de_pago' => 'Vino de Pago',
    ];

    protected $fillable = [
        'user_id',
        'oenologist_id',
        'name',
        'vintage',
        'wine_type',
        'aging_type',
        'category',
        'status',
        'variety',
        'volume_liters',
        'initial_quantity_kg',
        'internal_code',
        'trace_token',
        'is_must',
        'is_organic',
        'notes',
    ];

    protected $casts = [
        'vintage' => 'integer',
        'volume_liters' => 'decimal:3',
        'initial_quantity_kg' => 'decimal:3',
        'is_must' => 'boolean',
        'is_organic' => 'boolean',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Oenologist, $this> */
    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    /** @return HasMany<WineProcessDetail, $this> */
    public function processDetails(): HasMany
    {
        return $this->hasMany(WineProcessDetail::class)->orderBy('start_date');
    }

    /** Recepciones de uva que componen este lote */
    /** @return HasMany<WineHarvest, $this> */
    public function wineHarvests(): HasMany
    {
        return $this->hasMany(WineHarvest::class);
    }

    /** @return BelongsToMany<Harvest, $this> */
    public function harvests(): BelongsToMany
    {
        return $this->belongsToMany(Harvest::class, 'wine_harvests')
            ->withPivot(['quantity_kg', 'percentage'])
            ->withTimestamps();
    }

    /** @return HasMany<WineTransfer, $this> */
    public function transfers(): HasMany
    {
        return $this->hasMany(WineTransfer::class)->orderByDesc('transfer_date');
    }

    /** @return HasMany<WineLoss, $this> */
    public function losses(): HasMany
    {
        return $this->hasMany(WineLoss::class)->orderByDesc('loss_date');
    }

    /** @return HasMany<WineFermentationControl, $this> */
    public function fermentationControls(): HasMany
    {
        return $this->hasMany(WineFermentationControl::class)->orderByDesc('control_date');
    }

    /** @return HasMany<WineAnalysis, $this> */
    public function analyses(): HasMany
    {
        return $this->hasMany(WineAnalysis::class)->orderByDesc('analysis_date');
    }

    /** @return HasMany<WineAdditive, $this> */
    public function additives(): HasMany
    {
        return $this->hasMany(WineAdditive::class)->orderByDesc('application_date');
    }

    /** @return HasMany<WineBottling, $this> */
    public function bottlings(): HasMany
    {
        return $this->hasMany(WineBottling::class)->orderByDesc('bottling_date');
    }

    /** @return HasMany<WineLabeling, $this> */
    public function labelings(): HasMany
    {
        return $this->hasMany(WineLabeling::class)->orderByDesc('labeling_date');
    }

    /** @return HasMany<WineTastingNote, $this> */
    public function tastingNotes(): HasMany
    {
        return $this->hasMany(WineTastingNote::class)->orderByDesc('evaluation_date');
    }

    /** @return HasMany<WineSubproduct, $this> */
    public function subproducts(): HasMany
    {
        return $this->hasMany(WineSubproduct::class)->orderByDesc('subproduct_date');
    }

    /** @return HasMany<ProductLot, $this> */
    public function productLots(): HasMany
    {
        return $this->hasMany(ProductLot::class);
    }

    /** @return HasMany<WineCost, $this> */
    public function costs(): HasMany
    {
        return $this->hasMany(WineCost::class)->orderByDesc('cost_date');
    }

    /** Coste total de uva calculado desde wine_harvests × price_per_kg */
    public function getGrapeCostAttribute(): float
    {
        return (float) $this->wineHarvests()
            ->join('harvests', 'wine_harvests.harvest_id', '=', 'harvests.id')
            ->selectRaw('COALESCE(SUM(wine_harvests.quantity_kg * COALESCE(harvests.price_per_kg, 0)), 0) as total')
            ->value('total');
    }

    /** Suma de costes manuales registrados */
    public function getManualCostAttribute(): float
    {
        return (float) $this->costs()->sum('amount');
    }

    /** Coste total de producción (uva + costes manuales) */
    public function getTotalProductionCostAttribute(): float
    {
        return $this->grape_cost + $this->manual_cost;
    }

    /** Coste por litro. Null si no hay volumen. */
    public function getCostPerLiterAttribute(): ?float
    {
        $volume = (float) $this->volume_liters;
        if ($volume <= 0) {
            return null;
        }

        return $this->total_production_cost / $volume;
    }

    // ─── Helpers de contenedores ───────────────────────────────────────────────

    /**
     * Contenedores activos que tienen este vino según container_current_states.
     */
    public function activeContainers()
    {
        return Container::whereHas('currentStates', function ($q) {
            $q->where('wine_id', $this->id);
        })->active();
    }

    /**
     * Volumen total actual: suma de current_quantity en estados de contenedor.
     */
    public function getCurrentVolumeAttribute(): float
    {
        return (float) ContainerCurrentState::where('wine_id', $this->id)
            ->sum('current_quantity');
    }

    // ─── Helpers de estado ─────────────────────────────────────────────────────

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isAged(): bool
    {
        return $this->status === 'aged';
    }

    public function isBottled(): bool
    {
        return $this->status === 'bottled';
    }

    /**
     * Último análisis registrado.
     */
    public function latestAnalysis(): ?WineAnalysis
    {
        return $this->analyses()->first();
    }

    /**
     * Total de mermas acumuladas.
     */
    public function getTotalLossesAttribute(): float
    {
        return (float) $this->losses()->sum('quantity');
    }

    // ─── Opciones traducidas ──────────────────────────────────────────────────

    public static function wineTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::WINE_TYPES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    public static function agingTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::AGING_TYPES);
    }

    public static function categoryOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CATEGORIES);
    }

    // ─── Accessors de etiquetas ────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return __(self::WINE_TYPES[$this->wine_type]);
    }

    public function getStatusLabelAttribute(): string
    {
        return __(self::STATUSES[$this->status]);
    }

    public function getAgingTypeLabelAttribute(): ?string
    {
        return $this->aging_type ? __(self::AGING_TYPES[$this->aging_type] ?? $this->aging_type) : null;
    }

    public function getCategoryLabelAttribute(): ?string
    {
        return $this->category ? __(self::CATEGORIES[$this->category] ?? $this->category) : null;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['cancelled']);
    }

    protected static function booted(): void
    {
        static::creating(function (Wine $wine) {
            $wine->trace_token ??= (string) Str::uuid();
        });
    }
}
