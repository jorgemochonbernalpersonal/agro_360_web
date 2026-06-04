<?php

namespace App\Models;

use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContainerRoom extends Model
{
    use Auditable;
    use HasFactory;

    protected $auditExclude = ['created_at', 'updated_at'];

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
