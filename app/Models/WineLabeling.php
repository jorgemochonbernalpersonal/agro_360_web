<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineLabeling extends Model
{
    protected $fillable = [
        'user_id',
        'wine_id',
        'wine_bottling_id',
        'label_batch_id',
        'labeling_date',
        'quantity_labeled',
        'from_number',
        'to_number',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'labeling_date' => 'date',
        'quantity_labeled' => 'integer',
        'from_number' => 'integer',
        'to_number' => 'integer',
    ];

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

    /** @return BelongsTo<WineBottling, $this> */
    public function bottling(): BelongsTo
    {
        return $this->belongsTo(WineBottling::class, 'wine_bottling_id');
    }

    /** @return BelongsTo<LabelBatch, $this> */
    public function labelBatch(): BelongsTo
    {
        return $this->belongsTo(LabelBatch::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLabelRangeAttribute(): ?string
    {
        if (! $this->from_number || ! $this->to_number) {
            return null;
        }

        return number_format($this->from_number).' – '.number_format($this->to_number);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
