<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrewMember extends Model
{
    protected $table = 'crew_members';

    protected $fillable = [
        'crew_id',
        'viticulturist_id',
        'assigned_by',
    ];

    /**
     * Cuadrilla a la que pertenece (nullable para trabajadores individuales)
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class, 'crew_id');
    }

    /**
     * Verificar si es trabajador individual (sin cuadrilla)
     */
    public function isIndividual(): bool
    {
        return is_null($this->crew_id);
    }

    /**
     * Scope para trabajadores individuales de un viticultor
     */
    public function scopeIndividual($query, $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId)
                     ->whereNull('crew_id');
    }

    /**
     * Scope para trabajadores de un viticultor (individuales y en cuadrillas)
     */
    public function scopeForViticulturist($query, $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Viticultor miembro
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Usuario que asignó este miembro
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
