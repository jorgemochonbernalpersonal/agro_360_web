<?php

namespace App\Actions\Plot;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LockPlotAction
{
    /**
     * Bloquear una parcela
     */
    public function execute(Plot $plot, string $reason = 'Declaración PAC'): bool
    {
        if ($plot->isLocked()) {
            return false; // Ya está bloqueada
        }

        try {
            $plot->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => Auth::id(),
                'lock_reason' => $reason,
            ]);

            Log::info('Parcela bloqueada', [
                'plot_id' => $plot->id,
                'locked_by' => Auth::id(),
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al bloquear parcela', [
                'plot_id' => $plot->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
