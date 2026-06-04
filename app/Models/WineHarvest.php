<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineHarvest extends Model
{
    protected $fillable = [
        'wine_id',
        'harvest_id',
        'quantity_kg',
        'percentage',
    ];

    protected $casts = [
        'quantity_kg' => 'decimal:3',
        'percentage' => 'decimal:2',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }
}
