<?php

namespace App\Observers;

use App\Models\AgriculturalActivity;
use App\Models\AgriculturalActivityAuditLog;

class AgriculturalActivityObserver
{
    /**
     * Handle the AgriculturalActivity "created" event.
     */
    public function created(AgriculturalActivity $activity): void
    {
        AgriculturalActivityAuditLog::log($activity, 'created', [
            'new' => $activity->getAttributes(),
        ]);
        
        // Marcar paso de onboarding como completado
        if ($activity->viticulturist_id) {
            $progress = \App\Models\OnboardingProgress::getOrCreate(
                $activity->viticulturist_id,
                \App\Models\OnboardingProgress::STEP_REGISTER_ACTIVITY
            );
            
            if (!$progress->isCompleted()) {
                $progress->markAsCompleted();
            }
        }
    }

    /**
     * Handle the AgriculturalActivity "updating" event.
     * Verificar si está bloqueada antes de permitir actualización
     */
    public function updating(AgriculturalActivity $activity): bool
    {
        // Si está bloqueada, no permitir modificaciones
        if ($activity->is_locked && !$activity->isDirty('is_locked')) {
            throw new \Exception('No se puede modificar una actividad bloqueada. Desbloquéala primero.');
        }

        return true;
    }

    /**
     * Handle the AgriculturalActivity "updated" event.
     */
    public function updated(AgriculturalActivity $activity): void
    {
        // No registrar si solo se está bloqueando/desbloqueando
        // (ya se registra en los métodos lock/unlock)
        if ($activity->wasChanged(['is_locked', 'locked_at', 'locked_by']) && count($activity->getChanges()) <= 3) {
            return;
        }

        $changes = [];
        foreach ($activity->getChanges() as $key => $newValue) {
            if (in_array($key, ['updated_at'])) {
                continue; // Ignorar timestamps automáticos
            }
            
            $changes['old'][$key] = $activity->getOriginal($key);
            $changes['new'][$key] = $newValue;
        }

        if (!empty($changes)) {
            AgriculturalActivityAuditLog::log($activity, 'updated', $changes);
        }
    }

    /**
     * Handle the AgriculturalActivity "deleting" event.
     * Verificar si está bloqueada antes de permitir eliminación
     */
    public function deleting(AgriculturalActivity $activity): bool
    {
        if ($activity->is_locked) {
            throw new \Exception('No se puede eliminar una actividad bloqueada. Desbloquéala primero.');
        }

        return true;
    }

    /**
     * Handle the AgriculturalActivity "deleted" event.
     */
    public function deleted(AgriculturalActivity $activity): void
    {
        AgriculturalActivityAuditLog::log($activity, 'deleted', [
            'old' => $activity->getOriginal(),
        ]);
    }
}
