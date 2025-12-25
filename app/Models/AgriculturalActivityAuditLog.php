<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgriculturalActivityAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'activity_id',
        'user_id',
        'action',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Actividad auditada
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(AgriculturalActivity::class, 'activity_id');
    }

    /**
     * Usuario que realizó la acción
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crear log de auditoría
     * 
     * @param AgriculturalActivity $activity
     * @param string $action
     * @param array $changes
     * @return self
     */
    public static function log(AgriculturalActivity $activity, string $action, array $changes = []): self
    {
        return self::create([
            'activity_id' => $activity->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'changes' => $changes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }

    /**
     * Obtener descripción legible de la acción
     */
    public function getActionDescriptionAttribute(): string
    {
        $descriptions = [
            'created' => 'Actividad creada',
            'updated' => 'Actividad modificada',
            'deleted' => 'Actividad eliminada',
            'locked' => 'Actividad bloqueada',
            'unlocked' => 'Actividad desbloqueada',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }

    /**
     * Obtener resumen de cambios
     */
    public function getChangesSummaryAttribute(): array
    {
        if (!$this->changes || !isset($this->changes['old'], $this->changes['new'])) {
            return [];
        }

        $summary = [];
        $old = $this->changes['old'];
        $new = $this->changes['new'];

        foreach ($new as $key => $newValue) {
            $oldValue = $old[$key] ?? null;
            
            if ($oldValue != $newValue) {
                $summary[] = [
                    'field' => $key,
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $summary;
    }

    /**
     * Scope: Filtrar por actividad
     */
    public function scopeForActivity($query, int $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    /**
     * Scope: Filtrar por usuario
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filtrar por acción
     */
    public function scopeOfAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: Ordenar por más reciente
     */
    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
