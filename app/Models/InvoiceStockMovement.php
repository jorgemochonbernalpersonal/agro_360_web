<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceStockMovement extends Model
{
    protected $fillable = [
        'invoice_id',
        'invoice_item_id',
        'wine_lot_id',
        'user_id',
        'qty',
        'action',
        'from_bucket',
        'to_bucket',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function wineLot(): BelongsTo
    {
        return $this->belongsTo(WineLot::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
