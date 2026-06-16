<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
    /**
     * Pasos del onboarding enfocados en datos maestros esenciales
     * Estos son los mínimos necesarios para poder trabajar en la app
     */
    public const STEP_REVIEW_CAMPAIGN = 'review_campaign';

    public const STEP_CREATE_PLOT = 'create_plot';

    public const STEP_ADD_PRODUCTS = 'add_products';

    public const STEP_REGISTER_ACTIVITY = 'register_activity';

    /**
     * Todos los pasos en orden lógico
     * 1. Campaña (auto-creada, solo revisar)
     * 2. Parcelas (dato maestro esencial)
     * 3. Primera actividad (valor inmediato — entrada rápida)
     * 4. Productos fitosanitarios (dato maestro, puede hacerse después)
     */
    public const ALL_STEPS = [
        self::STEP_REVIEW_CAMPAIGN,      // 1. Revisar campaña activa
        self::STEP_CREATE_PLOT,          // 2. Crear parcela (dato maestro)
        self::STEP_REGISTER_ACTIVITY,    // 3. Primera actividad (¡ya puedes trabajar!)
        self::STEP_ADD_PRODUCTS,         // 4. Añadir productos (dato maestro)
    ];

    // Winery onboarding steps
    public const STEP_WINERY_FISCAL = 'winery_fiscal_data';

    public const STEP_WINERY_CONTAINERS = 'winery_add_containers';

    public const STEP_WINERY_VITICULTURIST = 'winery_link_viticulturist';

    public const STEP_WINERY_HARVEST = 'winery_first_harvest';

    public const STEP_WINERY_WINE = 'winery_first_wine';

    public const WINERY_STEPS = [
        self::STEP_WINERY_FISCAL,        // 1. Datos fiscales / configuración
        self::STEP_WINERY_CONTAINERS,    // 2. Añadir depósitos o barricas
        self::STEP_WINERY_VITICULTURIST, // 3. Vincular primer viticultor
        self::STEP_WINERY_HARVEST,       // 4. Primera recepción de uva
        self::STEP_WINERY_WINE,          // 5. Primer vino o lote
    ];

    // Supervisor (Denomination of Origin) onboarding steps
    public const STEP_SUPERVISOR_PROFILE = 'supervisor_configure_profile';

    public const STEP_SUPERVISOR_ADD_WINERY = 'supervisor_add_winery';

    public const STEP_SUPERVISOR_ADD_VITICULTURIST = 'supervisor_add_viticulturist';

    public const SUPERVISOR_STEPS = [
        self::STEP_SUPERVISOR_PROFILE,          // 1. Configurar perfil de la DO
        self::STEP_SUPERVISOR_ADD_WINERY,       // 2. Vincular primera bodega
        self::STEP_SUPERVISOR_ADD_VITICULTURIST, // 3. Añadir primer viticultor
    ];

    // Producer onboarding steps (campo + bodega combinados)
    public const STEP_PRODUCER_FISCAL = 'winery_fiscal_data';       // Reutiliza winery step

    public const STEP_PRODUCER_PLOT = 'create_plot';              // Reutiliza viticulturist step

    public const STEP_PRODUCER_CONTAINER = 'winery_add_containers';    // Reutiliza winery step

    public const STEP_PRODUCER_ACTIVITY = 'register_activity';        // Reutiliza viticulturist step

    public const STEP_PRODUCER_RECEPTION = 'winery_first_harvest';     // Reutiliza winery step

    public const PRODUCER_STEPS = [
        self::STEP_PRODUCER_FISCAL,     // 1. Datos fiscales
        self::STEP_PRODUCER_PLOT,       // 2. Primera parcela (campo)
        self::STEP_PRODUCER_CONTAINER,  // 3. Primer contenedor (bodega)
        self::STEP_PRODUCER_ACTIVITY,   // 4. Primera actividad de campo
        self::STEP_PRODUCER_RECEPTION,  // 5. Primera recepción (bodega)
    ];

    protected $table = 'onboarding_progress';

    protected $fillable = [
        'user_id',
        'step',
        'completed_at',
        'skipped',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'skipped' => 'boolean',
    ];

    /**
     * Usuario propietario del progreso
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si el paso está completado
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null || $this->skipped;
    }

    /**
     * Marcar paso como completado
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'completed_at' => now(),
            'skipped' => false,
        ]);
    }

    /**
     * Marcar paso como saltado
     */
    public function markAsSkipped(): void
    {
        $this->update([
            'skipped' => true,
            'completed_at' => now(),
        ]);
    }

    /**
     * Obtener o crear progreso para un usuario y paso
     */
    public static function getOrCreate(int $userId, string $step): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId, 'step' => $step],
            ['completed_at' => null, 'skipped' => false]
        );
    }

    /**
     * Verificar si todos los pasos están completados para un usuario
     */
    public static function isOnboardingComplete(int $userId): bool
    {
        $completedSteps = static::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();

        return $completedSteps >= count(self::ALL_STEPS);
    }

    /**
     * Obtener porcentaje de progreso para un usuario
     */
    public static function getProgressPercentage(int $userId): int
    {
        $completedSteps = static::where('user_id', $userId)
            ->whereNotNull('completed_at')
            ->count();

        return (int) (($completedSteps / count(self::ALL_STEPS)) * 100);
    }

    /**
     * Saltar todo el onboarding para un usuario
     */
    public static function skipAll(int $userId): void
    {
        foreach (self::ALL_STEPS as $step) {
            $progress = static::getOrCreate($userId, $step);
            if (! $progress->isCompleted()) {
                $progress->markAsSkipped();
            }
        }
    }

    /**
     * Resetear el onboarding (eliminar todo el progreso)
     * Útil si el usuario quiere volver a ver el tour
     */
    public static function resetOnboarding(int $userId): void
    {
        static::where('user_id', $userId)->delete();
    }
}
