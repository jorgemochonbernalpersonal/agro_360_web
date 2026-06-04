<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplyPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'supply_id',
        'viticulturist_id',
        'campaign_id',
        'invoice_date',
        'invoice_number',
        'quantity',
        'unit_of_measurement',
        'price_per_unit',
        'total_cost',
        'supplier_name',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'quantity' => 'decimal:3',
        'price_per_unit' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function supply(): BelongsTo
    {
        return $this->belongsTo(Supply::class);
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    protected static function booted(): void
    {
        // Al crear una compra, incrementar el stock del insumo
        static::created(function (SupplyPurchase $purchase) {
            $purchase->supply->increment('current_stock', $purchase->quantity);
        });

        // Al eliminar una compra, decrementar el stock
        static::deleted(function (SupplyPurchase $purchase) {
            $purchase->supply->decrement('current_stock', $purchase->quantity);
        });
    }
}
