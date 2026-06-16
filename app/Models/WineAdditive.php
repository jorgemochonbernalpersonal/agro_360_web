<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineAdditive extends Model
{
    protected $fillable = [
        'wine_id',
        'wine_process_detail_id',
        'winery_supply_id',
        'oenologist_id',
        'unit_of_measurement_id',
        'additive_name',
        'quantity',
        'application_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'application_date' => 'date',
    ];

    // ── Relations ──────────────────────────────────────────────────────────────

    /** @return BelongsTo<Wine, $this> */
    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    /** @return BelongsTo<WineProcessDetail, $this> */
    public function processDetail(): BelongsTo
    {
        return $this->belongsTo(WineProcessDetail::class, 'wine_process_detail_id');
    }

    /** @return BelongsTo<WinerySupply, $this> */
    public function supply(): BelongsTo
    {
        return $this->belongsTo(WinerySupply::class, 'winery_supply_id');
    }

    /** @return BelongsTo<Oenologist, $this> */
    public function oenologist(): BelongsTo
    {
        return $this->belongsTo(Oenologist::class);
    }

    /** @return BelongsTo<UnitOfMeasurement, $this> */
    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
