<?php

namespace App\Actions\Plot;

use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UnlockPlotAction
{
    /**
     * Desbloquear una parcela
     */
    public function execute(Plot $plot): bool
    {
        if (! $plot->isLocked()) {
            return false; // No está bloqueada
        }

        try {
            $plot->update([
                'is_locked' => false,
                'locked_at' => null,
                'locked_by' => null,
                'lock_reason' => null,
            ]);

            Log::info('Parcela desbloqueada', [
                'plot_id' => $plot->id,
                'unlocked_by' => Auth::id(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al desbloquear parcela', [
                'plot_id' => $plot->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
