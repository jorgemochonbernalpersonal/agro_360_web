<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlannedWork extends Model
{
    // ── Constantes ───────────────────────────────────────────────────────��───

    public const CATEGORIES = [
        'tratamiento' => 'Tratamiento Fitosanitario',
        'fertilizacion' => 'Fertilización',
        'riego' => 'Riego',
        'labor_cultural' => 'Labor Cultural',
        'poda' => 'Poda',
        'vendimia' => 'Vendimia',
        'observacion' => 'Observación',
        'post_vendimia' => 'Post-Vendimia',
        'otro' => 'Otro',
    ];

    public const PRIORITIES = [
        'baja' => 'Baja',
        'media' => 'Media',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'en_progreso' => 'En progreso',
        'completada' => 'Completada',
        'cancelada' => 'Cancelada',
    ];

    public const PRIORITY_COLORS = [
        'baja' => 'zinc',
        'media' => 'blue',
        'alta' => 'amber',
        'urgente' => 'red',
    ];

    public const STATUS_COLORS = [
        'pendiente' => 'amber',
        'en_progreso' => 'blue',
        'completada' => 'green',
        'cancelada' => 'zinc',
    ];

    public const CATEGORY_ICONS = [
        'tratamiento' => 'shield-exclamation',
        'fertilizacion' => 'funnel',
        'riego' => 'arrows-pointing-in',
        'labor_cultural' => 'wrench-screwdriver',
        'poda' => 'scissors',
        'vendimia' => 'archive-box-arrow-down',
        'observacion' => 'eye',
        'post_vendimia' => 'archive-box',
        'otro' => 'pencil-square',
    ];

    protected $fillable = [
        'viticulturist_id',
        'campaign_id',
        'plot_id',
        'category',
        'title',
        'description',
        'planned_date',
        'planned_end_date',
        'priority',
        'status',
        'linked_entry_type',
        'linked_entry_id',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'planned_end_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public static function categoryOptions(): array
    {
        return array_map(fn ($v) => __($v), static::CATEGORIES);
    }

    public static function priorityOptions(): array
    {
        return array_map(fn ($v) => __($v), static::PRIORITIES);
    }

    public static function statusOptions(): array
    {
        return array_map(fn ($v) => __($v), static::STATUSES);
    }

    // ── Relaciones ───────────────────────────────────────────────────────────

    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /** @return BelongsTo<Plot, $this> */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    public function linkedEntry(): MorphTo
    {
        return $this->morphTo('linked_entry');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->category] ?? ucfirst($this->category));
    }

    public function getPriorityLabelAttribute(): string
    {
        return __(self::PRIORITIES[$this->priority] ?? ucfirst($this->priority));
    }

    public function getStatusLabelAttribute(): string
    {
        return __(self::STATUSES[$this->status] ?? ucfirst($this->status));
    }

    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITY_COLORS[$this->priority] ?? 'zinc';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'zinc';
    }

    public function getCategoryIconAttribute(): string
    {
        return self::CATEGORY_ICONS[$this->category] ?? 'pencil-square';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pendiente'
            && $this->planned_date->isPast();
    }

    public function getIsLinkedAttribute(): bool
    {
        return $this->linked_entry_type !== null && $this->linked_entry_id !== null;
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopePending($query)
    {
        return $query->where('status', 'pendiente');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'en_progreso');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completada');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pendiente')
            ->whereDate('planned_date', '<', now());
    }

    public function scopeUpcoming($query, int $days = 7)
    {
        return $query->where('status', 'pendiente')
            ->whereDate('planned_date', '>=', now())
            ->whereDate('planned_date', '<=', now()->addDays($days));
    }

    // ── Acciones ─────────────────────────────────────────────────────────────

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completada',
            'completed_at' => now(),
        ]);
    }

    public function markCancelled(): void
    {
        $this->update(['status' => 'cancelada']);
    }

    public function markInProgress(): void
    {
        $this->update(['status' => 'en_progreso']);
    }
}
