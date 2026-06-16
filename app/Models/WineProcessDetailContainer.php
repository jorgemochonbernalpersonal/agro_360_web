<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class WineProcessDetailContainer extends Pivot
{
    protected $table = 'wine_process_detail_containers';

    protected $fillable = [
        'wine_process_detail_id',
        'container_id',
        'quantity',
        'unit_of_measurement_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    /** @return BelongsTo<WineProcessDetail, $this> */
    public function processDetail(): BelongsTo
    {
        return $this->belongsTo(WineProcessDetail::class, 'wine_process_detail_id');
    }

    /** @return BelongsTo<Container, $this> */
    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    /** @return BelongsTo<UnitOfMeasurement, $this> */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }
}
