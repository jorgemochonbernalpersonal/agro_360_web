<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelBatch extends Model
{
    const SOURCES = [
        'own' => 'Propio',
        'do_assigned' => 'Asignado por DO',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'user_id',
        'wine_id',
        'name',
        'source',
        'start_number',
        'end_number',
        'total_quantity',
        'used_quantity',
        'wasted_quantity',
        'notes',
    ];

    protected $casts = [
        'start_number' => 'integer',
        'end_number' => 'integer',
        'total_quantity' => 'integer',
        'used_quantity' => 'integer',
        'wasted_quantity' => 'integer',
    ];

    public static function sourceOptions(): array
    {
        return array_map(fn ($v) => __($v), static::SOURCES);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function labelings(): HasMany
    {
        return $this->hasMany(WineLabeling::class);
    }

    public function wastes(): HasMany
    {
        return $this->hasMany(LabelWaste::class)->orderByDesc('waste_date');
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->used_quantity - $this->wasted_quantity);
    }

    public function getUsagePercentAttribute(): float
    {
        if ($this->total_quantity === 0) {
            return 0;
        }

        return round(($this->used_quantity + $this->wasted_quantity) / $this->total_quantity * 100, 1);
    }

    public function getSourceLabelAttribute(): string
    {
        return __(self::SOURCES[$this->source] ?? $this->source ?? 'Propio');
    }

    public function isEmpty(): bool
    {
        return $this->available_quantity === 0;
    }

    public function canDelete(): bool
    {
        return $this->used_quantity === 0 && $this->wasted_quantity === 0;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithStock($query)
    {
        return $query->whereRaw('(total_quantity - used_quantity - wasted_quantity) > 0');
    }
}
