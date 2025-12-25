<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlotAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'plot_id',
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
     * Parcela auditada
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
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
     */
    public static function log(Plot $plot, string $action, array $changes = []): self
    {
        return self::create([
            'plot_id' => $plot->id,
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
            'created' => 'Parcela creada',
            'updated' => 'Parcela modificada',
            'deleted' => 'Parcela eliminada',
        ];

        return $descriptions[$this->action] ?? $this->action;
    }

    /**
     * Scope: Filtrar por parcela
     */
    public function scopeForPlot($query, int $plotId)
    {
        return $query->where('plot_id', $plotId);
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
