<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExternalGrapePurchase extends Model
{
    const PRODUCT_TYPES = [
        'grape'             => 'Uva',
        'must'              => 'Mosto',
        'concentrated_must' => 'Mosto concentrado',
        'rectified_must'    => 'Mosto concentrado rectificado',
    ];

    const STATUSES = [
        'pending'   => 'Pendiente',
        'received'  => 'Recibido',
        'processed' => 'Procesado',
        'rejected'  => 'Rechazado',
    ];

    protected $fillable = [
        'user_id',
        'supplier_id',
        'destination_container_id',
        'wine_id',
        'supplier_name',
        'product_type',
        'variety',
        'origin',
        'vintage_year',
        'quantity_kg',
        'price_per_kg',
        'total_price',
        'purchase_date',
        'delivery_date',
        'rega_code',
        'quality_certificate',
        'baume_degree',
        'brix_degree',
        'potential_alcohol',
        'acidity_level',
        'ph_level',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'purchase_date'    => 'date',
        'delivery_date'    => 'date',
        'quantity_kg'      => 'decimal:3',
        'price_per_kg'     => 'decimal:4',
        'total_price'      => 'decimal:2',
        'baume_degree'     => 'decimal:2',
        'brix_degree'      => 'decimal:2',
        'potential_alcohol' => 'decimal:2',
        'acidity_level'    => 'decimal:2',
        'ph_level'         => 'decimal:2',
        'vintage_year'     => 'integer',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function destinationContainer(): BelongsTo
    {
        return $this->belongsTo(Container::class, 'destination_container_id');
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Accessors ──────────────────────────────────────────────────────────────

    public function getProductTypeLabelAttribute(): string
    {
        return self::PRODUCT_TYPES[$this->product_type] ?? $this->product_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getSupplierDisplayAttribute(): string
    {
        return $this->supplier?->name ?? $this->supplier_name ?? '—';
    }

    // ── Scopes ─────────────────────────────────────────────────────────────────

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('vintage_year', $year);
    }
}
