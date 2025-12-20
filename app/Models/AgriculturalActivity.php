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
        'viticulturist_id',
        'campaign_id',
        'activity_type',
        'activity_date',
        'crew_id',
        'crew_member_id',
        'machinery_id',
        'weather_conditions',
        'temperature',
        'notes',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'temperature' => 'decimal:2',
    ];

    /**
     * Parcela donde se realizó la actividad
     */
    public function plot(): BelongsTo
    {
        return $this->belongsTo(Plot::class);
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
}
