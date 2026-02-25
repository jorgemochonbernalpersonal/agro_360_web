<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvisoryMembership extends Model
{
    use HasFactory;

    const SPECIALTIES = [
        'phytosanitary'  => 'Asesor fitosanitario (PI)',
        'agronomy'       => 'Agronomía general',
        'oenology'       => 'Enología',
        'sustainability' => 'Sostenibilidad / Certificaciones',
        'other'          => 'Otro',
    ];

    protected $fillable = [
        'viticulturist_id',
        'campaign_id',
        'advisor_name',
        'license_number',
        'specialty',
        'company_name',
        'phone',
        'email',
        'active',
    ];

    protected $casts = [
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

    public function getSpecialtyLabelAttribute(): string
    {
        return self::SPECIALTIES[$this->specialty] ?? $this->specialty;
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }
}
