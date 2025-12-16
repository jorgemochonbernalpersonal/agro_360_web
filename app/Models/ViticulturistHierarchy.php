<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ViticulturistHierarchy extends Model
{
    protected $table = 'viticulturist_hierarchy';

    protected $fillable = [
        'parent_viticulturist_id',
        'child_viticulturist_id',
        'winery_id',
        'assigned_by',
    ];

    /**
     * Viticultor padre (quien asigna)
     */
    public function parentViticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_viticulturist_id');
    }

    /**
     * Viticultor hijo (asignado)
     */
    public function childViticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'child_viticulturist_id');
    }

    /**
     * Bodega contexto
     */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /**
     * Usuario que asignó esta relación
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
