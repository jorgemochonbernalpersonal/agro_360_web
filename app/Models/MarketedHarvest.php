<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketedHarvest extends Model
{
    use HasFactory;

    const DESTINATION_TYPES = [
        'own_winery' => 'Bodega propia',
        'cooperative' => 'Cooperativa',
        'third_party' => 'Terceros',
        'other' => 'Otro',
    ];

    protected $fillable = [
        'harvest_id',
        'campaign_id',
        'viticulturist_id',
        'delivery_date',
        'quantity_kg',
        'destination_type',
        'buyer_name',
        'buyer_rega_code',
        'transport_document',
        'vehicle_plate',
        'price_per_kg',
        'total_value',
        'notes',
        'active',
        'invoice_id',
    ];

    protected $casts = [
        'delivery_date' => 'date',
        'quantity_kg' => 'decimal:3',
        'price_per_kg' => 'decimal:4',
        'total_value' => 'decimal:2',
        'active' => 'boolean',
    ];

    public static function destinationTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::DESTINATION_TYPES);
    }

    /** @return BelongsTo<Harvest, $this> */
    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Invoice, $this> */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getDestinationTypeLabelAttribute(): string
    {
        return __(self::DESTINATION_TYPES[$this->destination_type] ?? $this->destination_type);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }
}
