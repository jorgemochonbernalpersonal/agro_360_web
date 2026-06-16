<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property mixed $nif_cif
 */
class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'address',
        'city',
        'postal_code',
        'province_id',
        'country',
        'phone',
        'profile_image',
    ];

    /**
     * Relación con el usuario
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con la provincia
     */
    /** @return BelongsTo<Province, $this> */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }
}
