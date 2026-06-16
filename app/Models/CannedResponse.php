<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $body
 * @property mixed $title
 * @property mixed $sort_order
 */
class CannedResponse extends Model
{
    protected $fillable = ['admin_id', 'title', 'body', 'category', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /** @return BelongsTo<User, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
