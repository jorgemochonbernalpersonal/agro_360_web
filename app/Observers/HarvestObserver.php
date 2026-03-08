<?php

namespace App\Observers;

use App\Models\EstimatedYield;
use App\Models\Harvest;
use App\Services\ContainerStockService;
use Illuminate\Support\Facades\Log;

class HarvestObserver
{
    public function __construct(
        private ContainerStockService $stockService
    ) {}

    /**
     * Al crear una cosecha: registra stock inicial, actualiza contenedor y sincroniza estimaciones.
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

        $this->syncEstimatedYields($harvest);
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

        $weightChanged    = $oldWeight != $newWeight;
        $containerChanged = $oldContainerId != $newContainerId;

        try {
            if ($weightChanged && $containerChanged) {
                // Caso combinado: ajustar peso en el contenedor VIEJO y luego transferir el nuevo peso
                $this->stockService->adjustAndTransfer($harvest, $oldWeight, $newWeight, $oldContainerId, $newContainerId);
            } elseif ($weightChanged) {
                $this->stockService->adjustWeight($harvest, $oldWeight, $newWeight);
            } elseif ($containerChanged) {
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
     * Al actualizar una cosecha: si cambia peso o estado, re-sincroniza estimaciones.
     */
    public function updated(Harvest $harvest): void
    {
        if ($harvest->wasChanged(['total_weight', 'status', 'vintage'])) {
            $this->syncEstimatedYields($harvest);
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

    /**
     * Al eliminar (después): re-sincroniza estimaciones con el nuevo total.
     */
    public function deleted(Harvest $harvest): void
    {
        $this->syncEstimatedYields($harvest);
    }

    /**
     * Actualiza el rendimiento real en todas las EstimatedYield que coincidan
     * con la plantación y añada de esta cosecha.
     */
    private function syncEstimatedYields(Harvest $harvest): void
    {
        if (!$harvest->plot_planting_id || !$harvest->vintage) {
            return;
        }

        try {
            EstimatedYield::where('plot_planting_id', $harvest->plot_planting_id)
                ->whereHas('campaign', fn($q) => $q->where('year', $harvest->vintage))
                ->each(fn(EstimatedYield $ey) => $ey->updateActualYield());
        } catch (\Exception $e) {
            Log::warning('[HarvestObserver] Error al sincronizar rendimiento estimado', [
                'harvest_id'      => $harvest->id,
                'plot_planting_id'=> $harvest->plot_planting_id,
                'vintage'         => $harvest->vintage,
                'error'           => $e->getMessage(),
            ]);
        }
    }
}
