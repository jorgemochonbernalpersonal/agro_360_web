<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhytosanitaryContainerReturn extends Model
{
    const CONTAINER_TYPES = [
        'plastic' => 'Plástico (HDPE/PET)',
        'glass' => 'Vidrio',
        'metal' => 'Metal / Chapa',
        'cardboard' => 'Cartón / Papel',
        'flexible' => 'Flexible / Bolsa',
        'other' => 'Otro',
    ];

    const COLLECTION_SYSTEMS = [
        'sigfito' => 'SIGFITO',
        'field' => 'FIELD',
        'other' => 'Otro sistema',
    ];

    protected $fillable = [
        'viticulturist_id',
        'campaign_id',
        'phytosanitary_product_id',
        'date',
        'product_name',
        'registration_number',
        'container_type',
        'container_size_liters',
        'containers_quantity',
        'total_weight_kg',
        'collection_system',
        'collection_point',
        'transport_document',
        'notes',
        'active',
    ];

    protected $casts = [
        'date' => 'date',
        'container_size_liters' => 'decimal:3',
        'containers_quantity' => 'integer',
        'total_weight_kg' => 'decimal:3',
        'active' => 'boolean',
    ];

    public static function containerTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CONTAINER_TYPES);
    }

    public static function collectionSystemOptions(): array
    {
        return array_map(fn ($v) => __($v), static::COLLECTION_SYSTEMS);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<PhytosanitaryProduct, $this> */
    public function phytosanitaryProduct(): BelongsTo
    {
        return $this->belongsTo(PhytosanitaryProduct::class);
    }

    public function getContainerTypeLabelAttribute(): string
    {
        return __(self::CONTAINER_TYPES[$this->container_type]);
    }

    public function getCollectionSystemLabelAttribute(): string
    {
        return __(self::COLLECTION_SYSTEMS[$this->collection_system]);
    }

    public function scopeForViticulturist($query, int $id)
    {
        return $query->where('viticulturist_id', $id);
    }

    public function scopeForCampaign($query, int $id)
    {
        return $query->where('campaign_id', $id);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
