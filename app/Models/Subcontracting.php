<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcontracting extends Model
{
    use HasFactory;

    public const SERVICE_TYPES = [
        'harvesting' => 'Vendimia',
        'pruning' => 'Poda',
        'treatment' => 'Tratamientos fitosanitarios',
        'fertilization' => 'Abonado / Fertilización',
        'irrigation' => 'Riego',
        'soil_work' => 'Laboreo del suelo',
        'transport' => 'Transporte',
        'analysis' => 'Análisis / Asesoría',
        'other' => 'Otros servicios',
    ];

    protected $fillable = [
        'viticulturist_id',
        'plot_id',
        'campaign_id',
        'service_type',
        'company_name',
        'contact_person',
        'contact_phone',
        'service_date',
        'service_end_date',
        'amount',
        'invoiced',
        'invoice_number',
        'description',
        'notes',
    ];

    protected $casts = [
        'service_date' => 'date',
        'service_end_date' => 'date',
        'amount' => 'decimal:2',
        'invoiced' => 'boolean',
    ];

    public static function serviceTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::SERVICE_TYPES);
    }

    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return __(self::SERVICE_TYPES[$this->service_type] ?? $this->service_type);
    }
}
