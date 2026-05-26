<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineCost extends Model
{
    const CATEGORIES = [
        'additives'            => __('Aditivos enológicos'),
        'bottling'             => __('Embotellado y packaging'),
        'labeling'             => __('Etiquetado'),
        'analysis'             => __('Análisis de laboratorio'),
        'oenologist'           => __('Honorarios enólogo'),
        'container_maintenance'=> __('Mantenimiento contenedores'),
        'transport'            => __('Transporte y logística'),
        'storage'              => __('Almacenamiento y crianza'),
        'other'                => __('Otros costes'),
    ];

    const CATEGORY_COLORS = [
        'additives'            => 'blue',
        'bottling'             => 'violet',
        'labeling'             => 'rose',
        'analysis'             => 'cyan',
        'oenologist'           => 'indigo',
        'container_maintenance'=> 'orange',
        'transport'            => 'amber',
        'storage'              => 'emerald',
        'other'                => 'zinc',
    ];

    protected $fillable = [
        'wine_id',
        'user_id',
        'category',
        'description',
        'amount',
        'cost_date',
        'supplier',
        'invoice_reference',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'cost_date' => 'date',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
