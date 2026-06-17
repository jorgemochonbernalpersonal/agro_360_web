<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'description',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<ProductStock, $this> */
    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    /** @return HasMany<Supply, $this> */
    public function supplies(): HasMany
    {
        return $this->hasMany(Supply::class);
    }
}
