<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineBottlingSupply extends Model
{
    protected $fillable = [
        'wine_bottling_id',
        'winery_supply_id',
        'supply_name',
        'quantity',
        'unit_of_measurement_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    /** @return BelongsTo<WineBottling, $this> */
    public function bottling(): BelongsTo
    {
        return $this->belongsTo(WineBottling::class, 'wine_bottling_id');
    }

    /** @return BelongsTo<WinerySupply, $this> */
    public function winerySupply(): BelongsTo
    {
        return $this->belongsTo(WinerySupply::class);
    }

    /** @return BelongsTo<UnitOfMeasurement, $this> */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }
}
