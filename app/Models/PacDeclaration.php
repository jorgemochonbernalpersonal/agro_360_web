<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PacDeclaration extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const ECO_SCHEMES = [
        'cover_crops' => 'Cubiertas vegetales',
        'no_tillage' => 'Mínimo/no laboreo',
        'organic' => 'Producción ecológica',
        'integrated_pest_mgmt' => 'Gestión integrada de plagas',
        'reduced_phyto' => 'Reducción de fitosanitarios',
        'biodiversity' => 'Elementos de biodiversidad',
        'precision_fertilization' => 'Fertilización de precisión',
    ];

    protected $fillable = [
        'viticulturist_id',
        'year',
        'reference_number',
        'status',
        'submitted_at',
        'total_declared_area',
        'total_eligible_area',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'submitted_at' => 'datetime',
        'total_declared_area' => 'decimal:3',
        'total_eligible_area' => 'decimal:3',
    ];

    public static function ecoSchemeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ECO_SCHEMES);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return HasMany<PacDeclarationItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(PacDeclarationItem::class, 'declaration_id');
    }

    public function recalculateTotals(): void
    {
        $this->update([
            'total_declared_area' => $this->items()->sum('declared_area'),
            'total_eligible_area' => $this->items()->sum('eligible_area'),
        ]);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function statusLabel(): string
    {
        return __(match ($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_SUBMITTED => 'Presentada',
            self::STATUS_APPROVED => 'Aprobada',
            self::STATUS_REJECTED => 'Rechazada',
            default => $this->status,
        });
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'amber',
            self::STATUS_SUBMITTED => 'blue',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'zinc',
        };
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }
}
