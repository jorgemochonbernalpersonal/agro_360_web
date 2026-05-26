<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subcontracting extends Model
{
    use HasFactory;

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
        'service_date'     => 'date',
        'service_end_date' => 'date',
        'amount'           => 'decimal:2',
        'invoiced'         => 'boolean',
    ];

    public const SERVICE_TYPES = [
        'harvesting'    => __('Vendimia'),
        'pruning'       => __('Poda'),
        'treatment'     => __('Tratamientos fitosanitarios'),
        'fertilization' => __('Abonado / Fertilización'),
        'irrigation'    => __('Riego'),
        'soil_work'     => __('Laboreo del suelo'),
        'transport'     => __('Transporte'),
        'analysis'      => __('Análisis / Asesoría'),
        'other'         => __('Otros servicios'),
    ];

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
        return self::SERVICE_TYPES[$this->service_type] ?? $this->service_type;
    }
}
