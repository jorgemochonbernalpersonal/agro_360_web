<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotCost extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'labor' => 'Mano de obra',
        'machinery' => 'Maquinaria',
        'materials' => 'Materiales',
        'phytosanitary' => 'Fitosanitarios',
        'fertilizer' => 'Abonos y fertilizantes',
        'water' => 'Agua / Riego',
        'insurance' => 'Seguros',
        'transport' => 'Transporte',
        'subcontracting' => 'Subcontratación',
        'other' => 'Otros',
    ];

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
        'amount' => 'decimal:2',
        'cost_date' => 'date',
    ];

    public static function categoryOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CATEGORIES);
    }

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Plot, $this> */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->category] ?? $this->category);
    }
}
