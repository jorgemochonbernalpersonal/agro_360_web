<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineFermentationControl extends Model
{
    protected $fillable = [
        'wine_id',
        'container_id',
        'control_date',
        'temperature',
        'brix_degree',
        'baume_degree',
        'density',
        'ph',
        'volatile_acidity',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'control_date' => 'datetime',
        'temperature' => 'decimal:2',
        'brix_degree' => 'decimal:2',
        'baume_degree' => 'decimal:2',
        'density' => 'decimal:4',
        'ph' => 'decimal:2',
        'volatile_acidity' => 'decimal:2',
    ];

    /** @return BelongsTo<Wine, $this> */
    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Indica si la fermentación está activa (densidad > 1000 o brix > 2).
     */
    public function isFermenting(): bool
    {
        if ($this->density !== null) {
            return (float) $this->density > 1.000;
        }
        if ($this->brix_degree !== null) {
            return (float) $this->brix_degree > 2;
        }

        return false;
    }
}
