<?php

namespace App\Observers;

use App\Models\WineLoss;

/**
 * Mantiene sincronizado Wine.volume_liters cuando se registra o elimina una merma.
 *
 * NOTA: La actualización de Container.wine_volume_liters y ContainerHistory
 * la gestiona exclusivamente WineContainerStockService::recordLoss / revertLoss.
 * Este observer NO toca Container.used_capacity (campo reservado para cosechas/uva).
 */
class WineLossObserver
{
    public function created(WineLoss $loss): void
    {
        $wine = $loss->wine;
        if ($wine) {
            $wine->volume_liters = max(0, (float) $wine->volume_liters - (float) $loss->quantity);
            $wine->saveQuietly();
        }
    }

    public function deleted(WineLoss $loss): void
    {
        $wine = $loss->wine;
        if ($wine) {
            $wine->volume_liters = (float) $wine->volume_liters + (float) $loss->quantity;
            $wine->saveQuietly();
        }
    }
}
