<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PacDeclarationItem extends Model
{
    protected $fillable = [
        'declaration_id',
        'plot_id',
        'declared_area',
        'eligible_area',
        'eco_schemes',
        'notes',
    ];

    protected $casts = [
        'declared_area' => 'decimal:3',
        'eligible_area' => 'decimal:3',
        'eco_schemes'   => 'array',
    ];

    public function declaration(): BelongsTo
    {
        return $this->belongsTo(PacDeclaration::class, 'declaration_id');
    }

    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function ecoSchemeLabels(): array
    {
        if (empty($this->eco_schemes)) return [];
        return array_map(
            fn($key) => PacDeclaration::ECO_SCHEMES[$key] ?? $key,
            $this->eco_schemes
        );
    }
}
