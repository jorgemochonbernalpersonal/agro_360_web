<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineLoss extends Model
{
    const LOSS_TYPES = [
        'evaporation' => __('Evaporación / Merma natural'),
        'filtration'  => __('Filtración'),
        'sampling'    => __('Muestreo / Analítica'),
        'spillage'    => __('Derrame accidental'),
        'other'       => __('Otro'),
    ];

    const LOSS_AUTHORIZATIONS = [
        'authorized'    => __('Pérdida autorizada'),
        'processing'    => __('Pérdida por proceso'),
        'extraordinary' => __('Pérdida extraordinaria'),
        'quality'       => __('Rechazo por calidad'),
    ];

    protected $fillable = [
        'wine_id',
        'container_id',
        'loss_type',
        'loss_authorization',
        'regulatory_reference',
        'quantity',
        'unit_of_measurement_id',
        'loss_date',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity'  => 'decimal:3',
        'loss_date' => 'date',
    ];

    public function wine(): BelongsTo
    {
        return $this->belongsTo(Wine::class);
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function unitOfMeasurement(): BelongsTo
    {
        return $this->belongsTo(UnitOfMeasurement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getLossTypeLabelAttribute(): string
    {
        return self::LOSS_TYPES[$this->loss_type] ?? $this->loss_type;
    }

    public function getLossAuthorizationLabelAttribute(): string
    {
        return self::LOSS_AUTHORIZATIONS[$this->loss_authorization] ?? ($this->loss_authorization ?? '—');
    }
}
