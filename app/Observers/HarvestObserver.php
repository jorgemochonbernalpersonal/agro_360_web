<?php

namespace App\Observers;

use App\Models\Harvest;
use App\Services\ContainerStockService;
use Illuminate\Support\Facades\Log;

class HarvestObserver
{
    public function __construct(
        private ContainerStockService $stockService
    ) {}

    /**
     * Al crear una cosecha: registra stock inicial y actualiza el contenedor.
     */
    public function created(Harvest $harvest): void
    {
        try {
            $this->stockService->initializeStock($harvest);
        } catch (\Exception $e) {
            Log::error('[HarvestObserver] Error al inicializar stock', [
                'harvest_id' => $harvest->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Al actualizar una cosecha: maneja cambios de peso y/o contenedor.
     */
    public function updating(Harvest $harvest): void
    {
        $oldWeight      = (float) $harvest->getOriginal('total_weight');
        $newWeight      = (float) $harvest->total_weight;
        $oldContainerId = $harvest->getOriginal('container_id');
        $newContainerId = $harvest->container_id;

        try {
            if ($oldWeight != $newWeight) {
                $this->stockService->adjustWeight($harvest, $oldWeight, $newWeight);
            }

            if ($oldContainerId != $newContainerId) {
                $this->stockService->transferContainer($harvest, $oldContainerId, $newContainerId);
            }
        } catch (\Exception $e) {
            Log::error('[HarvestObserver] Error al actualizar cosecha', [
                'harvest_id' => $harvest->id,
                'error'      => $e->getMessage(),
            ]);
            throw $e; // Re-throw para que el update falle si el contenedor no tiene capacidad
        }
    }

    /**
     * Al eliminar una cosecha: libera la capacidad del contenedor.
     */
    public function deleting(Harvest $harvest): void
    {
        try {
            $this->stockService->releaseHarvestStock($harvest);
        } catch (\Exception $e) {
            Log::error('[HarvestObserver] Error al liberar stock de cosecha eliminada', [
                'harvest_id' => $harvest->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
