<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAbility extends Model
{
    protected $table = 'user_abilities';

    protected $fillable = ['user_id', 'ability_id', 'granted_by', 'granted_at'];

    protected $casts = ['granted_at' => 'datetime'];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Ability, $this> */
    public function ability(): BelongsTo
    {
        return $this->belongsTo(Ability::class);
    }

    /** @return BelongsTo<User, $this> */
    public function grantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }
}
