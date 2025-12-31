<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingProgress extends Model
{
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
     * 3. Productos fitosanitarios (dato maestro para tratamientos)
     * 4. Primera actividad (ya puedes trabajar)
     */
    public const ALL_STEPS = [
        self::STEP_REVIEW_CAMPAIGN,      // 1. Revisar campaña activa
        self::STEP_CREATE_PLOT,          // 2. Crear parcela (dato maestro)
        self::STEP_ADD_PRODUCTS,         // 3. Añadir productos (dato maestro)
        self::STEP_REGISTER_ACTIVITY,    // 4. Primera actividad (¡ya puedes trabajar!)
    ];

    /**
     * Usuario propietario del progreso
     */
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
            if (!$progress->isCompleted()) {
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
