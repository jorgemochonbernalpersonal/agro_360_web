<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineContainerStockEntry extends Model
{
    const SOURCES = [
        'initial_stock' => __('Stock inicial'),
        'adjustment'    => __('Ajuste de inventario'),
        'correction'    => __('Corrección'),
    ];

    protected $fillable = [
        'wine_id',
        'container_id',
        'quantity_liters',
        'entry_date',
        'source',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity_liters' => 'decimal:3',
        'entry_date'      => 'date',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getSourceLabelAttribute(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }
}
