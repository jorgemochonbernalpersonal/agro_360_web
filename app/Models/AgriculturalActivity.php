<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property mixed $humidity
 * @property mixed $wind_speed
 */
class AgriculturalActivity extends Model
{
    use HasFactory;

    // Tipos de actividad disponibles
    const ACTIVITY_TYPES = [
        'phytosanitary' => 'Tratamiento fitosanitario',
        'fertilization' => 'Fertilización',
        'irrigation' => 'Riego',
        'cultural' => 'Labor cultural',
        'observation' => 'Observación',
        'harvest' => 'Cosecha',
        'pruning' => 'Poda',
        'phenology' => 'Observación fenológica',
        'post_harvest' => 'Tratamiento post-vendimia',
    ];

    protected $fillable = [
        'plot_id',
        'plot_planting_id',
        'viticulturist_id',
        'winery_viticulturist_id',  // ← FK a winery_viticulturist
        'campaign_id',
        'activity_type',
        'phenological_stage',  // Estadio fenológico
        'activity_date',
        'crew_id',
        'crew_member_id',
        'machinery_id',
        'weather_conditions',
        'temperature',
        'notes',
        'is_locked',
        'locked_at',
        'locked_by',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'temperature' => 'decimal:2',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public static function activityTypeOptions(): array
    {
        return array_map(fn ($v) => __($v), static::ACTIVITY_TYPES);
    }

    public static function activityTypes(): array
    {
        return static::activityTypeOptions();
    }

    /**
     * Parcela donde se realizó la actividad
     */
    /** @return BelongsTo<Plot, $this> */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Plantación donde se realizó la actividad
     */
    /** @return BelongsTo<PlotPlanting, $this> */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    /**
     * Viticultor que realizó la actividad
     */
    /** @return BelongsTo<User, $this> */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Relación con bodega (si fue creado por viticultor invitado)
     */
    /** @return BelongsTo<WineryViticulturist, $this> */
    public function wineryRelation(): BelongsTo
    {
        return $this->belongsTo(WineryViticulturist::class, 'winery_viticulturist_id');
    }

    /**
     * Campaña a la que pertenece la actividad
     */
    /** @return BelongsTo<Campaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Cuadrilla que realizó la actividad
     */
    /** @return BelongsTo<Crew, $this> */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Trabajador individual que realizó la actividad (opcional)
     */
    /** @return BelongsTo<CrewMember, $this> */
    public function crewMember(): BelongsTo
    {
        return $this->belongsTo(CrewMember::class, 'crew_member_id');
    }

    /**
     * Tratamiento fitosanitario (si activity_type es 'phytosanitary')
     */
    /** @return HasOne<PhytosanitaryTreatment, $this> */
    public function phytosanitaryTreatment(): HasOne
    {
        return $this->hasOne(PhytosanitaryTreatment::class, 'activity_id');
    }

    /**
     * Fertilización (si activity_type es 'fertilization')
     */
    /** @return HasOne<Fertilization, $this> */
    public function fertilization(): HasOne
    {
        return $this->hasOne(Fertilization::class, 'activity_id');
    }

    /**
     * Riego (si activity_type es 'irrigation')
     */
    /** @return HasOne<Irrigation, $this> */
    public function irrigation(): HasOne
    {
        return $this->hasOne(Irrigation::class, 'activity_id');
    }

    /**
     * Labor cultural (si activity_type es 'cultural')
     */
    /** @return HasOne<CulturalWork, $this> */
    public function culturalWork(): HasOne
    {
        return $this->hasOne(CulturalWork::class, 'activity_id');
    }

    /**
     * Observación (si activity_type es 'observation')
     */
    /** @return HasOne<Observation, $this> */
    public function observation(): HasOne
    {
        return $this->hasOne(Observation::class, 'activity_id');
    }

    /**
     * Cosecha (si activity_type es 'harvest')
     */
    /** @return HasOne<Harvest, $this> */
    public function harvest(): HasOne
    {
        return $this->hasOne(Harvest::class, 'activity_id');
    }

    /**
     * Tratamiento post-vendimia (si activity_type es 'post_harvest')
     */
    /** @return HasOne<PostHarvestTreatment, $this> */
    public function postHarvestTreatment(): HasOne
    {
        return $this->hasOne(PostHarvestTreatment::class, 'activity_id');
    }

    /**
     * Maquinaria utilizada en la actividad
     */
    /** @return BelongsTo<Machinery, $this> */
    public function machinery(): BelongsTo
    {
        return $this->belongsTo(Machinery::class, 'machinery_id');
    }

    /**
     * Logs de auditoría de la actividad
     */
    public function auditLogs()
    {
        return $this->hasMany(AgriculturalActivityAuditLog::class, 'activity_id')->orderBy('created_at', 'desc');
    }

    /**
     * Usuario que bloqueó la actividad
     */
    /** @return BelongsTo<User, $this> */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Verificar si la actividad está bloqueada
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Bloquear actividad (impedir modificaciones)
     */
    public function lock(?int $userId = null): void
    {
        if ($this->is_locked) {
            return;
        }

        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => $userId ?? auth()->id(),
        ]);

        AgriculturalActivityAuditLog::log($this, 'locked');
    }

    /**
     * Desbloquear actividad.
     * Solo administradores pueden desbloquear — las actividades se bloquean
     * para cumplimiento PAC y no deben desbloquearse por el viticultor.
     */
    public function unlock(): void
    {
        if (! $this->is_locked) {
            return;
        }

        if (! \Illuminate\Support\Facades\Auth::user()?->isAdmin()) {
            throw new \RuntimeException('Solo los administradores pueden desbloquear actividades.');
        }

        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
        ]);

        AgriculturalActivityAuditLog::log($this, 'unlocked');
    }

    /**
     * Scope para filtrar por tipo de actividad
     *
     * @param mixed $query
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope para filtrar actividades del viticultor
     *
     * @param mixed $query
     */
    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Scope para filtrar por usuario (alias genérico de forViticulturist)
     *
     * @param mixed $query
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('viticulturist_id', $userId);
    }

    /**
     * Scope para filtrar por parcela
     *
     * @param mixed $query
     */
    public function scopeForPlot($query, int $plotId)
    {
        return $query->where('plot_id', $plotId);
    }

    /**
     * Scope para filtrar por campaña
     *
     * @param mixed $query
     */
    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para filtrar por plantación
     *
     * @param mixed $query
     */
    public function scopeForPlanting($query, int $plotPlantingId)
    {
        return $query->where('plot_planting_id', $plotPlantingId);
    }
}
