<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

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
     * @param int|null $userId ID del usuario (opcional, si no se proporciona usa auth()->id() o viticulturist_id)
     * @return self
     */
    public static function log(AgriculturalActivity $activity, string $action, array $changes = [], ?int $userId = null): self
    {
        // Si no hay userId proporcionado, intentar obtenerlo de auth() o usar el viticulturist_id de la actividad
        if ($userId === null) {
            try {
                $authUser = \Illuminate\Support\Facades\Auth::user();
                $userId = $authUser ? $authUser->id : null;
            } catch (\Exception $e) {
                $userId = null;
            }

            // Si no hay usuario autenticado, usar el viticulturist_id de la actividad
            if ($userId === null && isset($activity->viticulturist_id)) {
                $userId = $activity->viticulturist_id;
            }
        }

        // Si aún no hay userId, usar 1 como fallback (admin o sistema)
        if ($userId === null) {
            $userId = 1;
        }

        $request = request();
        $ipAddress = '127.0.0.1';
        $userAgent = 'Seeder';

        if ($request) {
            try {
                $ipAddress = $request->ip() ?? '127.0.0.1';
                $userAgent = $request->userAgent() ?? 'Seeder';
            } catch (\Exception $e) {
                // Si falla, usar valores por defecto
            }
        }

        return self::create([
            'activity_id' => $activity->id,
            'user_id' => $userId,
            'action' => $action,
            'changes' => $changes,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
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
