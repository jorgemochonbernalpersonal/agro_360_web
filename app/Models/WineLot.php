<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WineLot extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'vintage',
        'wine_type',
        'quantity',
        'unit',
        'available_quantity',
        'reserved_quantity',
        'sold_quantity',
        'price_per_unit',
        'notes',
        'archived',
    ];

    protected $casts = [
        'vintage'            => 'integer',
        'quantity'           => 'decimal:3',
        'available_quantity' => 'decimal:3',
        'reserved_quantity'  => 'decimal:3',
        'sold_quantity'      => 'decimal:3',
        'price_per_unit'     => 'decimal:4',
        'archived'           => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(InvoiceStockMovement::class);
    }

    public function getFillPercentAttribute(): float
    {
        if ($this->quantity <= 0) return 0;
        return round((float) $this->sold_quantity / (float) $this->quantity * 100, 1);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeActive($query)
    {
        return $query->where('archived', false);
    }
}
