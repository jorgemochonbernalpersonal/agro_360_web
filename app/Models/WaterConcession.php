<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaterConcession extends Model
{
    const CONCESSION_TYPES = [
        'superficial' => 'Aguas superficiales',
        'subterranea' => 'Aguas subterráneas',
        'comunidad_regantes' => 'Comunidad de regantes',
        'otro' => 'Otro',
    ];

    protected $fillable = [
        'viticulturist_id', 'campaign_id',
        'concession_type', 'concession_number', 'water_body', 'authority',
        'concession_date', 'expiry_date',
        'max_volume_m3', 'used_volume_m3', 'surface_ha',
        'notes', 'active',
    ];

    protected $casts = [
        'concession_date' => 'date',
        'expiry_date' => 'date',
        'max_volume_m3' => 'decimal:3',
        'used_volume_m3' => 'decimal:3',
        'surface_ha' => 'decimal:4',
        'active' => 'boolean',
    ];

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getConcessionTypeLabelAttribute(): string
    {
        return self::CONCESSION_TYPES[$this->concession_type] ?? $this->concession_type;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date
            && ! $this->expiry_date->isPast()
            && now()->diffInDays($this->expiry_date) <= 90;
    }
}
