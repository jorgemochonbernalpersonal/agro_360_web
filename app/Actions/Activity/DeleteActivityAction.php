<?php

namespace App\Actions\Activity;

use App\Models\AgriculturalActivity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeleteActivityAction
{
    /**
     * Eliminar una actividad agrícola
     */
    public function execute(AgriculturalActivity $activity): bool
    {
        // Verificar autorización
        if (!Auth::user()->can('delete', $activity)) {
            throw new \Exception(__('No tienes permiso para eliminar esta actividad.'));
        }

        // Verificar si está bloqueada
        if ($activity->isLocked()) {
            throw new \Exception(__('No se puede eliminar una actividad bloqueada.'));
        }

        try {
            $activityId = $activity->id;
            $activity->delete();

            Log::info('Actividad eliminada', [
                'activity_id' => $activityId,
                'deleted_by' => Auth::id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al eliminar actividad', [
                'activity_id' => $activity->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
