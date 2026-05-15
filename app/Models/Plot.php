<?php

namespace App\Models;

use App\Models\Builders\PlotQueryBuilder;
use App\Models\PlotAlertPreference;
use App\Models\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    use HasFactory, Auditable;
    
    protected $fillable = [
        'name',
        'description',
        // Identificación
        'viticulturist_id',
        'owner_id',
        // Superficies
        'area',
        'cadastral_area',
        'pac_eligible_area',
        'non_eligible_area',
        'eligibility_coefficient',
        // Ubicación
        'autonomous_community_id',
        'province_id',
        'municipality_id',
        'site_id',
        'valley_id',
        // Catastro / identificación
        'code_parcel',
        'enclosure',
        // Tipo de suelo y topografía
        'soil_type_id',
        'irrigation_type_id',
        'topography_id',
        'orientation_id',
        'slope',
        // Tipo de propiedad
        'property_type_id',
        // Plantación
        'plantation_year',
        'training_system_id',
        'planting_pattern',
        'number_of_vines',
        'is_organic',
        // Vendimia
        'maximum_yield_kg_ha',
        'degree_day_base',
        // Estado
        'active',
        'is_locked',
        'locked_at',
        'locked_by',
        'lock_reason',
        // Alertas
        'ndvi_alert_threshold',
        'alert_email_enabled',
    ];

    protected $casts = [
        'area' => 'decimal:3',
        'pac_eligible_area' => 'decimal:3',
        'non_eligible_area' => 'decimal:3',
        'eligibility_coefficient' => 'decimal:4',
        'active' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'alert_email_enabled' => 'boolean',
        'maximum_yield_kg_ha' => 'decimal:3',
        'degree_day_base' => 'decimal:1',
        'cadastral_area' => 'decimal:4',
        'plantation_year' => 'integer',
        'is_organic' => 'boolean',
        'slope' => 'decimal:2',
        'number_of_vines' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Plot $plot) {
            if ($plot->area > 0 && $plot->pac_eligible_area !== null) {
                $plot->eligibility_coefficient = round(
                    (float) $plot->pac_eligible_area / (float) $plot->area,
                    4
                );
            }
        });
    }

    /**
     * Usar Query Builder personalizado
     */
    public function newEloquentBuilder($query): PlotQueryBuilder
    {
        return new PlotQueryBuilder($query);
    }

    /**
     * Viticultor asignado a la parcela
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function soilType(): BelongsTo
    {
        return $this->belongsTo(SoilType::class);
    }

    public function irrigationType(): BelongsTo
    {
        return $this->belongsTo(IrrigationType::class);
    }

    public function topography(): BelongsTo
    {
        return $this->belongsTo(Topography::class);
    }

    public function orientation(): BelongsTo
    {
        return $this->belongsTo(Orientation::class);
    }

    public function propertyType(): BelongsTo
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function valleyZone(): BelongsTo
    {
        return $this->belongsTo(Valley::class, 'valley_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function trainingSystem(): BelongsTo
    {
        return $this->belongsTo(TrainingSystem::class);
    }

    /**
     * Comunidad autónoma
     */
    public function autonomousCommunity(): BelongsTo
    {
        return $this->belongsTo(AutonomousCommunity::class, 'autonomous_community_id');
    }

    /**
     * Provincia
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    /**
     * Municipio
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class, 'municipality_id');
    }

    /**
     * Usos SIGPAC (many-to-many)
     */
    public function sigpacUses(): BelongsToMany
    {
        return $this->belongsToMany(SigpacUse::class, 'plot_sigpac_use', 'plot_id', 'sigpac_use_id');
    }

    /**
     * Códigos SIGPAC (nueva estructura - many-to-many con geometrías)
     */
    public function sigpacCodes(): BelongsToMany
    {
        return $this->belongsToMany(SigpacCode::class, 'multipart_plot_sigpac', 'plot_id', 'sigpac_code_id')
            ->withPivot('plot_geometry_id')
            ->withTimestamps();
    }

    /**
     * Relaciones múltiples plot-sigpac (para acceder a geometrías)
     */
    public function multiplePlotSigpacs(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'plot_id');
    }

    /**
     * Geometrías de la parcela (via multiple_plot_sigpac)
     */
    public function plotGeometries()
    {
        return $this->hasManyThrough(
            PlotGeometry::class,
            MultipartPlotSigpac::class,
            'plot_id',          // FK en multiple_plot_sigpac
            'id',               // FK en plot_geometry
            'id',               // Local key en plots
            'plot_geometry_id'  // Local key en multiple_plot_sigpac
        );
    }


    /**
     * Actividades agrícolas de la parcela
     */
    public function agriculturalActivities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'plot_id');
    }

    /**
     * Plantaciones de variedades de uva en la parcela
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(PlotPlanting::class);
    }

    // ── Datos agregados desde plantaciones ───────────────────────────────

    /**
     * Año de plantación más antiguo entre todas las plantaciones activas.
     */
    public function getOldestPlantingYearAttribute(): ?int
    {
        return $this->plantings()->where('status', 'active')->min('planting_year');
    }

    /**
     * Total de cepas sumando todas las plantaciones activas.
     */
    public function getTotalVinesAttribute(): ?int
    {
        $sum = $this->plantings()->where('status', 'active')->sum('vine_count');
        return $sum > 0 ? (int) $sum : null;
    }

    /**
     * Sistemas de conducción únicos de las plantaciones activas.
     */
    public function getTrainingSystemNamesAttribute(): string
    {
        return $this->plantings()
            ->where('status', 'active')
            ->whereNotNull('training_system_id')
            ->with('trainingSystem')
            ->get()
            ->pluck('trainingSystem.name')
            ->unique()
            ->implode(', ');
    }

    // ELIMINADO - ahora se usa el trait Auditable

    /**
     * Datos de teledetección de la parcela
     */
    public function remoteSensingData(): HasMany
    {
        return $this->hasMany(PlotRemoteSensing::class);
    }

    public function alertPreferences(): HasMany
    {
        return $this->hasMany(PlotAlertPreference::class);
    }

    /**
     * Último dato de teledetección
     */
    public function latestRemoteSensing()
    {
        return $this->hasOne(PlotRemoteSensing::class)->latestOfMany('image_date');
    }

    /**
     * Usuario que bloqueó la parcela
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    /**
     * Bloquear la parcela
     */
    public function lock(string $reason = 'Declaración PAC'): void
    {
        $this->update([
            'is_locked' => true,
            'locked_at' => now(),
            'locked_by' => auth()->id(),
            'lock_reason' => $reason,
        ]);
    }

    /**
     * Desbloquear la parcela
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'locked_at' => null,
            'locked_by' => null,
            'lock_reason' => null,
        ]);
    }

    /**
     * Verificar si la parcela está bloqueada
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Tratamientos fitosanitarios de la parcela
     */
    public function phytosanitaryTreatments()
    {
        return $this->hasManyThrough(
            PhytosanitaryTreatment::class,
            AgriculturalActivity::class,
            'plot_id', // Foreign key en agricultural_activities
            'activity_id', // Foreign key en phytosanitary_treatments
            'id', // Local key en plots
            'id' // Local key en agricultural_activities
        );
    }

    /**
     * Última actividad agrícola
     */
    public function lastAgriculturalActivity()
    {
        return $this->hasOne(AgriculturalActivity::class, 'plot_id')
            ->latestOfMany('activity_date');
    }

    /**
     * Último tratamiento fitosanitario
     */
    public function lastPhytosanitaryTreatment()
    {
        return $this->hasOne(AgriculturalActivity::class, 'plot_id')
            ->where('activity_type', 'phytosanitary')
            ->latestOfMany('activity_date')
            ->with('phytosanitaryTreatment');
    }

    /**
     * Tratamientos con plazo de seguridad activo (optimizado)
     * NOTA: Este método permanece en el modelo porque opera sobre una instancia
     */
    public function activeWithdrawalPeriods()
    {
        $today = now();
        
        return $this->agriculturalActivities()
            ->where('activity_type', 'phytosanitary')
            ->whereHas('phytosanitaryTreatment.product', function($query) {
                $query->whereNotNull('withdrawal_period_days');
            })
            ->with(['phytosanitaryTreatment.product'])
            ->get()
            ->filter(function($activity) use ($today) {
                if (!$activity->phytosanitaryTreatment || !$activity->phytosanitaryTreatment->product) {
                    return false;
                }
                
                $withdrawalDays = $activity->phytosanitaryTreatment->product->withdrawal_period_days;
                $safeDate = $activity->activity_date->copy()->addDays($withdrawalDays);
                
                return $safeDate->isFuture();
            });
    }
}
