<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'year',
        'viticulturist_id',
        'start_date',
        'end_date',
        'active',
        'description',
    ];

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Viticultor propietario de la campaña
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Actividades agrícolas de esta campaña
     */
    public function activities(): HasMany
    {
        return $this->hasMany(AgriculturalActivity::class, 'campaign_id');
    }

    /**
     * Scope para filtrar campañas activas
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por año
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope para filtrar por viticultor
     */
    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Activar esta campaña y desactivar las demás del viticultor
     */
    public function activate(): void
    {
        // Desactivar todas las campañas del viticultor
        static::where('viticulturist_id', $this->viticulturist_id)
            ->where('id', '!=', $this->id)
            ->update(['active' => false]);

        // Activar esta campaña
        $this->update(['active' => true]);
    }

    /**
     * Obtener o crear la campaña activa del año actual para un viticultor
     * Retorna null si hay algún error (ej: tabla no existe)
     */
    public static function getOrCreateActiveForYear(int $viticulturistId, int $year = null): ?self
    {
        try {
            $year = $year ?? now()->year;

            // Buscar campaña activa del año
            $campaign = static::forViticulturist($viticulturistId)
                ->forYear($year)
                ->active()
                ->first();

            if ($campaign) {
                return $campaign;
            }

            // Buscar cualquier campaña del año (aunque no esté activa)
            $campaign = static::forViticulturist($viticulturistId)
                ->forYear($year)
                ->first();

            if ($campaign) {
                $campaign->activate();
                return $campaign;
            }

            // Crear nueva campaña
            $campaign = static::create([
                'name' => "Campaña {$year}",
                'year' => $year,
                'viticulturist_id' => $viticulturistId,
                'start_date' => now()->startOfYear(),
                'end_date' => now()->endOfYear(),
                'active' => true,
            ]);

            // Desactivar otras campañas del viticultor
            static::where('viticulturist_id', $viticulturistId)
                ->where('id', '!=', $campaign->id)
                ->update(['active' => false]);

            return $campaign;
        } catch (\Exception $e) {
            \Log::error('Error al obtener/crear campaña activa', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'year' => $year ?? now()->year,
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Retornar null en lugar de lanzar excepción
            return null;
        }
    }
}
