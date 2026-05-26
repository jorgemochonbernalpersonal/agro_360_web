<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'viticulturist_id',
        'plot_id',
        'campaign_id',
        'category',
        'description',
        'amount',
        'cost_date',
        'supplier',
        'invoice_reference',
        'notes',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'cost_date' => 'date',
    ];

    public const CATEGORIES = [
        'labor'          => __('Mano de obra'),
        'machinery'      => __('Maquinaria'),
        'materials'      => __('Materiales'),
        'phytosanitary'  => __('Fitosanitarios'),
        'fertilizer'     => __('Abonos y fertilizantes'),
        'water'          => __('Agua / Riego'),
        'insurance'      => __('Seguros'),
        'transport'      => __('Transporte'),
        'subcontracting' => __('Subcontratación'),
        'other'          => __('Otros'),
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

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
