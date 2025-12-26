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
    public const SOURCE_SELF = 'self'; // Viticultor que se registró públicamente

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

    /**
     * Scope para obtener viticultores visibles para un viticultor
     * Usa las relaciones existentes para optimizar queries
     */
    public function scopeVisibleTo($query, User $viticulturist, $wineryId = null)
    {
        if (!$viticulturist->isViticulturist()) {
            return $query->whereRaw('1 = 0');
        }
        
        // Usar atributo cacheado del supervisor (optimizado)
        $supervisor = $viticulturist->supervisor;
        $supervisorId = $supervisor?->id;
        
        // Usar atributo cacheado de wineries (optimizado)
        $wineries = $viticulturist->wineries;
        $wineryIds = $wineries->pluck('id');
        
        // SIEMPRE puede ver los viticultores que creó, incluso sin winery ni supervisor
        return $query->where(function ($q) use ($viticulturist, $supervisorId, $wineryIds, $wineryId) {
            // 1. Viticultores creados por este viticultor (SIEMPRE visibles)
            $q->where(function ($subQ) use ($viticulturist) {
                $subQ->where('parent_viticulturist_id', $viticulturist->id)
                     ->where('source', self::SOURCE_VITICULTURIST);
            });
            
            // 2. Si tiene supervisor: viticultores del pool del supervisor
            if ($supervisorId) {
                $q->orWhere(function ($subQ) use ($supervisorId, $wineryId) {
                    $subQ->where('source', self::SOURCE_SUPERVISOR)
                         ->where('supervisor_id', $supervisorId);
                    
                    if ($wineryId) {
                        $subQ->where('winery_id', $wineryId);
                    }
                });
            }
            
            // 3. Si tiene winery: viticultores de sus wineries
            if ($wineryIds->isNotEmpty()) {
                $q->orWhere(function ($subQ) use ($wineryIds, $wineryId) {
                    $subQ->whereIn('winery_id', $wineryIds)
                         ->where(function ($wineryQ) {
                             $wineryQ->where('source', self::SOURCE_OWN)
                                     ->orWhere('source', self::SOURCE_VITICULTURIST);
                         });
                    
                    if ($wineryId) {
                        $subQ->where('winery_id', $wineryId);
                    }
                });
            }
        })
        ->where('viticulturist_id', '!=', $viticulturist->id)
        ->when($wineryId, fn($q) => $q->where('winery_id', $wineryId))
        ->with(['viticulturist', 'winery', 'parentViticulturist']); // Eager loading para evitar N+1
    }

    /**
     * Scope para obtener viticultores que puede editar (solo los que creó)
     */
    public function scopeEditableBy($query, User $viticulturist)
    {
        if (!$viticulturist->isViticulturist()) {
            return $query->whereRaw('1 = 0');
        }
        
        return $query->where('parent_viticulturist_id', $viticulturist->id)
                     ->where('source', self::SOURCE_VITICULTURIST);
    }

    /**
     * Verificar si un viticultor es visible para otro viticultor
     */
    public function isVisibleTo(User $viticulturist): bool
    {
        if (!$viticulturist->isViticulturist()) {
            return false;
        }
        
        // Si es el mismo usuario, no es visible
        if ($this->viticulturist_id === $viticulturist->id) {
            return false;
        }
        
        // Si fue creado por este viticultor, siempre es visible
        if ($this->parent_viticulturist_id === $viticulturist->id && 
            $this->source === self::SOURCE_VITICULTURIST) {
            return true;
        }
        
        // Verificar si tiene supervisor y este viticultor viene de su pool
        $supervisor = $viticulturist->supervisor;
        if ($supervisor && 
            $this->source === self::SOURCE_SUPERVISOR && 
            $this->supervisor_id === $supervisor->id) {
            return true;
        }
        
        // Verificar si tiene winery y este viticultor está en su winery
        $wineries = $viticulturist->wineries;
        if ($wineries->isNotEmpty() && 
            $wineries->contains('id', $this->winery_id) &&
            ($this->source === self::SOURCE_OWN || $this->source === self::SOURCE_VITICULTURIST)) {
            return true;
        }
        
        return false;
    }
}
