<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read mixed $last_bottling_date
 */
class WineBottling extends Model
{
    const BOTTLE_FORMATS = [
        '187' => '187 ml (Split)',
        '375' => '375 ml (Media botella)',
        '500' => '500 ml',
        '750' => '750 ml (Estándar)',
        '1000' => '1000 ml (Litro)',
        '1500' => '1500 ml (Magnum)',
        '3000' => '3000 ml (Doble Magnum)',
        '5000' => '5000 ml (Jeroboam)',
        'otro' => 'Otro formato',
    ];

    protected $fillable = [
        'user_id',
        'wine_id',
        'container_id',
        'wine_process_detail_id',
        'product_lot_id',
        'oenologist_id',
        'bottling_date',
        'bottle_format',
        'quantity_bottles',
        'quantity_liters',
        'lot_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'bottling_date' => 'date',
        'quantity_liters' => 'decimal:3',
        'quantity_bottles' => 'integer',
    ];

    public static function bottleFormatOptions(): array
    {
        return array_map(fn ($v) => __($v), static::BOTTLE_FORMATS);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Wine, $this> */
    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    /** @return BelongsTo<Container, $this> */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /** @return BelongsTo<WineProcessDetail, $this> */
    public function processDetail(): BelongsTo
    {
        return $this->belongsTo(WineProcessDetail::class, 'wine_process_detail_id');
    }

    /** @return BelongsTo<ProductLot, $this> */
    public function productLot(): BelongsTo
    {
        return $this->belongsTo(ProductLot::class, 'product_lot_id');
    }

    /** @return BelongsTo<Oenologist, $this> */
    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<WineBottlingSupply, $this> */
    public function supplies(): HasMany
    {
        return $this->hasMany(WineBottlingSupply::class);
    }

    /** @return HasMany<WineLabeling, $this> */
    public function labelings(): HasMany
    {
        return $this->hasMany(WineLabeling::class);
    }

    public function getFormatLabelAttribute(): string
    {
        return self::BOTTLE_FORMATS[$this->bottle_format] ?? $this->bottle_format;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
