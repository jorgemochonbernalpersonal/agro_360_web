<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineStockSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'wine_id',
        'snapshot_date',
        'quantity_liters',
        'container_count',
        'alcohol_percentage',
        'vintage',
        'wine_type',
        'is_must',
        'observations',
        'created_by',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'quantity_liters' => 'decimal:3',
        'alcohol_percentage' => 'decimal:2',
        'is_must' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
