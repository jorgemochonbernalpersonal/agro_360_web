<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelWaste extends Model
{
    protected $fillable = [
        'label_batch_id',
        'user_id',
        'quantity',
        'from_number',
        'to_number',
        'reason',
        'waste_date',
        'created_by',
    ];

    protected $casts = [
        'waste_date' => 'date',
        'quantity' => 'integer',
        'from_number' => 'integer',
        'to_number' => 'integer',
    ];

    /** @return BelongsTo<LabelBatch, $this> */
    public function labelBatch(): BelongsTo
    {
        return $this->belongsTo(LabelBatch::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
