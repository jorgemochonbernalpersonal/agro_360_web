<?php

namespace App\Observers;

use App\Models\Wine;
use App\Models\WineLoss;

/**
 * Mantiene sincronizado Wine.volume_liters cuando se registra, edita o elimina una merma.
 *
 * NOTA: La actualización de Container.wine_volume_liters y ContainerHistory
 * la gestiona exclusivamente WineContainerStockService::recordLoss / revertLoss / updateLoss.
 * Este observer NO toca Container.used_capacity (campo reservado para cosechas/uva).
 */
class WineLossObserver
{
    public function created(WineLoss $loss): void
    {
        $wine = $loss->wine;
        if ($wine) {
            $lossQty = (float) $loss->quantity;
            $currentVolume = (float) $wine->volume_liters;

            if ($lossQty > $currentVolume) {
                throw new \RuntimeException(
                    "La merma ({$lossQty} L) supera el volumen actual del vino «{$wine->name}» ({$currentVolume} L)."
                );
            }

            $wine->volume_liters = $currentVolume - $lossQty;
            $wine->saveQuietly();
        }
    }

    /**
     * Al editar una merma: ajusta Wine.volume_liters por la diferencia de cantidad
     * y/o el cambio de vino.
     *
     * WineContainerStockService::updateLoss() ya gestiona Container.wine_volume_liters;
     * este método es el único responsable de mantener Wine.volume_liters coherente.
     */
    public function updated(WineLoss $loss): void
    {
        $oldWineId = (int) $loss->getOriginal('wine_id');
        $newWineId = (int) $loss->wine_id;
        $oldQty = (float) $loss->getOriginal('quantity');
        $newQty = (float) $loss->quantity;

        // Nada relevante cambió
        if ($oldWineId === $newWineId && $oldQty === $newQty) {
            return;
        }

        if ($oldWineId !== $newWineId) {
            // Cambió el vino: restaurar volumen al vino anterior y descontar al nuevo
            $oldWine = Wine::find($oldWineId);
            if ($oldWine) {
                $oldWine->volume_liters = (float) $oldWine->volume_liters + $oldQty;
                $oldWine->saveQuietly();
            }

            $newWine = Wine::find($newWineId);
            if ($newWine) {
                $newWine->volume_liters = max(0, (float) $newWine->volume_liters - $newQty);
                $newWine->saveQuietly();
            }
        } else {
            // Mismo vino, solo cambió la cantidad
            $diff = $newQty - $oldQty;
            $wine = $loss->wine;
            if ($wine) {
                $wine->volume_liters = max(0, (float) $wine->volume_liters - $diff);
                $wine->saveQuietly();
            }
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
