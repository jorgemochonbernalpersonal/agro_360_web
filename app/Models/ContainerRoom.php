<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'temperature',
        'humidity',
        'capacity',
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'decimal:2',
        'capacity' => 'integer',
    ];

    /**
     * Usuario propietario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Contenedores en esta sala
     */
    public function containers(): HasMany
    {
        return $this->hasMany(Container::class);
    }
}
