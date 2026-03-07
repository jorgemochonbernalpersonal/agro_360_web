<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wine extends Model
{
    const WINE_TYPES = [
        'red'        => 'Tinto',
        'white'      => 'Blanco',
        'rose'       => 'Rosado',
        'sparkling'  => 'Espumoso',
        'fortified'  => 'Generoso / Fortificado',
        'sweet'      => 'Dulce',
        'semi_sweet' => 'Semidulce',
        'other'      => 'Otro',
    ];

    const STATUSES = [
        'in_progress' => 'En elaboración',
        'aged'        => 'En crianza',
        'bottled'     => 'Embotellado',
        'sold'        => 'Vendido',
        'cancelled'   => 'Cancelado',
    ];

    protected $fillable = [
        'user_id',
        'name',
        'vintage',
        'wine_type',
        'status',
        'variety',
        'volume_liters',
        'internal_code',
        'notes',
    ];

    protected $casts = [
        'vintage'      => 'integer',
        'volume_liters'=> 'decimal:3',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processDetails(): HasMany
    {
        return $this->hasMany(WineProcessDetail::class)->orderBy('start_date');
    }

    public function getTypeLabelAttribute(): string
    {
        return self::WINE_TYPES[$this->wine_type] ?? $this->wine_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function currentContainers()
    {
        return Container::whereHas('wineProcessDetails', function ($q) {
            $q->where('wine_id', $this->id);
        });
    }
}
