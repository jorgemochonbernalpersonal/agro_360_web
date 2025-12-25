<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\CrewMember;

class AgriculturalActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'plot_id',
        'plot_planting_id',
        'viticulturist_id',
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

    /**
     * Parcela donde se realizó la actividad
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
    }

    /**
     * Plantación donde se realizó la actividad
     */
    public function plotPlanting(): BelongsTo
    {
        return $this->belongsTo(PlotPlanting::class, 'plot_planting_id');
    }

    /**
     * Viticultor que realizó la actividad
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Campaña a la que pertenece la actividad
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Cuadrilla que realizó la actividad
     */
    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }

    /**
     * Trabajador individual que realizó la actividad (opcional)
     */
    public function crewMember(): BelongsTo
    {
        return $this->belongsTo(CrewMember::class, 'crew_member_id');
    }

    /**
     * Tratamiento fitosanitario (si activity_type es 'phytosanitary')
     */
    public function phytosanitaryTreatment(): HasOne
    {
        return $this->hasOne(PhytosanitaryTreatment::class, 'activity_id');
    }

    /**
     * Fertilización (si activity_type es 'fertilization')
     */
    public function fertilization(): HasOne
    {
        return $this->hasOne(Fertilization::class, 'activity_id');
    }

    /**
     * Riego (si activity_type es 'irrigation')
     */
    public function irrigation(): HasOne
    {
        return $this->hasOne(Irrigation::class, 'activity_id');
    }

    /**
     * Labor cultural (si activity_type es 'cultural')
     */
    public function culturalWork(): HasOne
    {
        return $this->hasOne(CulturalWork::class, 'activity_id');
    }

    /**
     * Observación (si activity_type es 'observation')
     */
    public function observation(): HasOne
    {
        return $this->hasOne(Observation::class, 'activity_id');
    }

    /**
     * Cosecha (si activity_type es 'harvest')
     */
    public function harvest(): HasOne
    {
        return $this->hasOne(Harvest::class, 'activity_id');
    }

    /**
     * Maquinaria utilizada en la actividad
     */
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
     * Desbloquear actividad
     */
    public function unlock(): void
    {
        if (!$this->is_locked) {
            return;
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
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope para filtrar actividades del viticultor
     */
    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Scope para filtrar por usuario (alias genérico de forViticulturist)
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('viticulturist_id', $userId);
    }

    /**
     * Scope para filtrar por parcela
     */
    public function scopeForPlot($query, int $plotId)
    {
        return $query->where('plot_id', $plotId);
    }

    /**
     * Scope para filtrar por campaña
     */
    public function scopeForCampaign($query, int $campaignId)
    {
        return $query->where('campaign_id', $campaignId);
    }

    /**
     * Scope para filtrar por plantación
     */
    public function scopeForPlanting($query, int $plotPlantingId)
    {
        return $query->where('plot_planting_id', $plotPlantingId);
    }
}
