<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<parameter name="content"><?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wine extends Model
{
    const WINE_TYPES = [
        'red'        => 'Tinto',
        'white'      => 'Blanco',
        'rose'       => 'Rosado',
        'sparkling'  => 'Espumoso',
        'fortified'  => 'Generoso / Fortificado',
        'sweet'      => 'Dulce',
        'semi_sweet' => 'Semidulce',
        'other'      => 'Otro',
    ];

    const STATUSES = [
        'in_progress' => 'En elaboración',
        'aged'        => 'En crianza',
        'bottled'     => 'Embotellado',
        'sold'        => 'Vendido',
        'cancelled'   => 'Cancelado',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'vintage',
        'wine_type',
        'status',
        'variety',
        'volume_liters',
        'initial_quantity_kg',
        'internal_code',
        'notes',
    ];

    protected $casts = [
        'vintage'             => 'integer',
        'volume_liters'       => 'decimal:3',
        'initial_quantity_kg' => 'decimal:3',
    ];

    // ─── Relaciones ────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processDetails(): HasMany
    {
        return $this->hasMany(WineProcessDetail::class)->orderBy('start_date');
    }

    /** Recepciones de uva que componen este lote */
    public function wineHarvests(): HasMany
    {
        return $this->hasMany(WineHarvest::class);
    }

    public function harvests(): BelongsToMany
    {
        return $this->belongsToMany(Harvest::class, 'wine_harvests')
            ->withPivot(['quantity_kg', 'percentage'])
            ->withTimestamps();
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(WineTransfer::class)->orderByDesc('transfer_date');
    }

    public function losses(): HasMany
    {
        return $this->hasMany(WineLoss::class)->orderByDesc('loss_date');
    }

    public function fermentationControls(): HasMany
    {
        return $this->hasMany(WineFermentationControl::class)->orderByDesc('control_date');
    }

    public function analyses(): HasMany
    {
        return $this->hasMany(WineAnalysis::class)->orderByDesc('analysis_date');
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

    // ─── Accessors de etiquetas ────────────────────────────────────────────────

    public function getTypeLabelAttribute(): string
    {
        return self::WINE_TYPES[$this->wine_type] ?? $this->wine_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
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
}
