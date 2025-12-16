<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WineryViticulturist extends Model
{
    protected $table = 'winery_viticulturist';

    protected $fillable = [
        'winery_id',
        'viticulturist_id',
        'assigned_by',
        'source',
        'supervisor_id',
        'parent_viticulturist_id',
    ];

    /**
     * Fuentes posibles
     */
    public const SOURCE_OWN = 'own';
    public const SOURCE_SUPERVISOR = 'supervisor';
    public const SOURCE_VITICULTURIST = 'viticulturist';

    /**
     * Bodega que tiene este viticultor
     */
    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /**
     * Viticultor asignado a la bodega
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Usuario que asignó esta relación
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Supervisor de origen (si viene del pool del supervisor)
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Viticultor padre que creó este viticultor
     */
    public function parentViticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_viticulturist_id');
    }

    /**
     * Verificar si es propio de la bodega
     */
    public function isOwn(): bool
    {
        return $this->source === self::SOURCE_OWN;
    }

    /**
     * Verificar si viene del supervisor
     */
    public function isFromSupervisor(): bool
    {
        return $this->source === self::SOURCE_SUPERVISOR;
    }

    /**
     * Verificar si fue creado por un viticultor
     */
    public function isFromViticulturist(): bool
    {
        return $this->source === self::SOURCE_VITICULTURIST;
    }
}
