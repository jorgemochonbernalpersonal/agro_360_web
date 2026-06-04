<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'year',
        'viticulturist_id',
        'winery_viticulturist_id',  // ← FK a winery_viticulturist
        'start_date',
        'end_date',
        'active',
        'description',
        // Validaciones
        'mid_validation_signed',
        'mid_validation_date',
        'mid_validation_user_id',
        'final_validation_signed',
        'final_validation_date',
        'final_validation_user_id',
        'locked_at',
        'pdf_path',
    ];

    protected $casts = [
        'year' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'mid_validation_signed' => 'boolean',
        'mid_validation_date' => 'datetime',
        'final_validation_signed' => 'boolean',
        'final_validation_date' => 'datetime',
        'locked_at' => 'datetime',
    ];

    /**
     * Viticultor propietario de la campaña
     */
    public function viticulturist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'viticulturist_id');
    }

    /**
     * Relación con bodega (si fue creado por viticultor invitado)
     */
    public function wineryRelation(): BelongsTo
    {
        return $this->belongsTo(WineryViticulturist::class, 'winery_viticulturist_id');
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
     *
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para filtrar por año
     *
     * @param mixed $query
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope para filtrar por viticultor
     *
     * @param mixed $query
     */
    public function scopeForViticulturist($query, int $viticulturistId)
    {
        return $query->where('viticulturist_id', $viticulturistId);
    }

    /**
     * Activar esta campaña y desactivar las demás del viticultor.
     * Usa transacción para evitar race condition entre llamadas concurrentes.
     */
    public function activate(): void
    {
        DB::transaction(function () {
            static::where('viticulturist_id', $this->viticulturist_id)
                ->where('id', '!=', $this->id)
                ->update(['active' => false]);

            $this->update(['active' => true]);
        });
    }

    /**
     * Obtener o crear la campaña activa del año para un viticultor.
     * Usa transacción con lockForUpdate para prevenir duplicados en requests concurrentes.
     * Retorna null si hay algún error (ej: tabla no existe).
     */
    public static function getOrCreateActiveForYear(int $viticulturistId, ?int $year = null): ?self
    {
        $year = $year ?? now()->year;

        try {
            return DB::transaction(function () use ($viticulturistId, $year) {
                // lockForUpdate bloquea el gap en InnoDB, evitando inserciones concurrentes
                $campaign = static::forViticulturist($viticulturistId)
                    ->forYear($year)
                    ->active()
                    ->lockForUpdate()
                    ->first();

                if ($campaign) {
                    return $campaign;
                }

                // Buscar cualquier campaña del año (aunque no esté activa)
                $campaign = static::forViticulturist($viticulturistId)
                    ->forYear($year)
                    ->lockForUpdate()
                    ->first();

                if ($campaign) {
                    $campaign->activate();

                    return $campaign;
                }

                // Crear nueva campaña y desactivar el resto en la misma transacción
                $campaign = static::create([
                    'name' => "Campaña {$year}",
                    'year' => $year,
                    'viticulturist_id' => $viticulturistId,
                    'start_date' => now()->startOfYear(),
                    'end_date' => now()->endOfYear(),
                    'active' => true,
                ]);

                static::where('viticulturist_id', $viticulturistId)
                    ->where('id', '!=', $campaign->id)
                    ->update(['active' => false]);

                return $campaign;
            });
        } catch (\Exception $e) {
            \Log::error('Error al obtener/crear campaña activa', [
                'error' => $e->getMessage(),
                'viticulturist_id' => $viticulturistId,
                'year' => $year,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
