<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Plot extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'description',
        'viticulturist_id',
        'area',
        'active',
        'autonomous_community_id',
        'province_id',
        'municipality_id',
    ];

    protected $casts = [
        'area' => 'decimal:3',
        'active' => 'boolean',
    ];

    public function winery(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winery_id');
    }

    /**
     * Viticultor asignado a la parcela
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
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
     * Códigos SIGPAC (many-to-many)
     */
    public function sigpacCodes(): BelongsToMany
    {
        return $this->belongsToMany(SigpacCode::class, 'plot_sigpac_code', 'plot_id', 'sigpac_code_id');
    }

    /**
     * Coordenadas multiparte SIGPAC
     */
    public function multipartCoordinates(): HasMany
    {
        return $this->hasMany(MultipartPlotSigpac::class, 'plot_id');
    }

    /**
     * Actividades agrícolas de la parcela
     */
    public function agriculturalActivities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'plot_id');
    }

    /**
     * Alias para facilitar acceso a actividades
     */
    public function activities(): HasMany
    {
        return $this->agriculturalActivities();
    }

    /**
     * Plantaciones de variedades de uva en la parcela
     */
    public function plantings(): HasMany
    {
        return $this->hasMany(PlotPlanting::class);
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
     * Scope para filtrar parcelas según el usuario
     */
    public function scopeForUser($query, User $user)
    {
        return match ($user->role) {
            'admin' => $query,
            'supervisor' => $query->whereIn('viticulturist_id', function($q) use ($user) {
                // Obtener viticultores pertenecientes a las wineries supervisadas
                $q->select('viticulturist_id')
                  ->from('winery_viticulturist')
                  ->whereIn('winery_id', function($sq) use ($user) {
                      $sq->select('winery_id')
                         ->from('supervisor_winery')
                         ->where('supervisor_id', $user->id);
                  });
            }),
            'winery' => $query->whereIn('viticulturist_id', function($q) use ($user) {
                $q->select('viticulturist_id')
                  ->from('winery_viticulturist')
                  ->where('winery_id', $user->id);
            }),
            'viticulturist' => $query->forViticulturist($user),
            default => $query->whereRaw('1 = 0'),
        };
    }

    /**
     * Scope para filtrar parcelas visibles para un viticultor
     * Incluye sus propias parcelas y parcelas de viticultores visibles
     */
    public function scopeForViticulturist($query, User $user)
    {
        if (!$user->isViticulturist()) {
            return $query->whereRaw('1 = 0');
        }

        // Obtener IDs de viticultores visibles (cacheado)
        $supervisor = $user->supervisor;
        $supervisorId = $supervisor?->id;
        
        $wineries = $user->wineries;
        $wineryIds = $wineries->pluck('id');

        // Viticultores creados por este viticultor
        $createdViticulturistIds = WineryViticulturist::where('parent_viticulturist_id', $user->id)
            ->where('source', WineryViticulturist::SOURCE_VITICULTURIST)
            ->pluck('viticulturist_id');

        return $query->where(function($q) use ($user, $supervisorId, $wineryIds, $createdViticulturistIds) {
            // Sus propias parcelas
            $q->where('viticulturist_id', $user->id);
            
            // Parcelas de viticultores creados por él
            if ($createdViticulturistIds->isNotEmpty()) {
                $q->orWhereIn('viticulturist_id', $createdViticulturistIds);
            }
            
            // Parcelas de viticultores del supervisor (si tiene)
            if ($supervisorId) {
                $supervisorViticulturistIds = WineryViticulturist::where('source', WineryViticulturist::SOURCE_SUPERVISOR)
                    ->where('supervisor_id', $supervisorId)
                    ->pluck('viticulturist_id');
                
                if ($supervisorViticulturistIds->isNotEmpty()) {
                    $q->orWhereIn('viticulturist_id', $supervisorViticulturistIds);
                }
            }
            
            // Parcelas de viticultores de sus wineries (si tiene)
            if ($wineryIds->isNotEmpty()) {
                $wineryViticulturistIds = WineryViticulturist::whereIn('winery_id', $wineryIds)
                    ->where(function($wineryQ) {
                        $wineryQ->where('source', WineryViticulturist::SOURCE_OWN)
                                ->orWhere('source', WineryViticulturist::SOURCE_VITICULTURIST);
                    })
                    ->pluck('viticulturist_id');
                
                if ($wineryViticulturistIds->isNotEmpty()) {
                    $q->orWhereIn('viticulturist_id', $wineryViticulturistIds);
                }
            }
        });
    }

    /**
     * Tratamientos con plazo de seguridad activo (optimizado)
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
