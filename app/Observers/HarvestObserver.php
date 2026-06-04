<?php

namespace App\Observers;

use App\Models\EstimatedYield;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Notifications\HarvestDeliveryAutoDisputedNotification;
use App\Notifications\HarvestDeliveryMatchedNotification;
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
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }

        $this->syncEstimatedYields($harvest);

        // Si es una recepción de bodega, intentar enlazar con la entrega declarada del viticultor
        if ($harvest->isWineryReception() && $harvest->batch_id) {
            try {
                $batch = GrapeReceptionBatch::find($harvest->batch_id);
                if ($batch) {
                    $this->linkHarvestDelivery($harvest, $batch);
                }
            } catch (\Exception $e) {
                Log::warning('[HarvestObserver] Error al enlazar HarvestDelivery', [
                    'harvest_id' => $harvest->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Al actualizar una cosecha: maneja cambios de peso y/o contenedor.
     */
    public function updating(Harvest $harvest): void
    {
        $oldWeight = (float) $harvest->getOriginal('total_weight');
        $newWeight = (float) $harvest->total_weight;
        $oldContainerId = $harvest->getOriginal('container_id');
        $newContainerId = $harvest->container_id;

        $weightChanged = $oldWeight != $newWeight;
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
                'error' => $e->getMessage(),
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

        // Si la recepción de bodega pasa a cancelada, liberar la HarvestDelivery enlazada
        if ($harvest->isWineryReception() && $harvest->wasChanged('status') && $harvest->isCancelled()) {
            HarvestDelivery::where('harvest_id', $harvest->id)->update([
                'harvest_id' => null,
                'status' => 'pending',
                'discrepancy_kg' => null,
                'dispute_note' => null,
                'dispute_submitted_at' => null,
                'dispute_resolution_note' => null,
                'dispute_resolved_at' => null,
            ]);
        }

        // Si cambia el peso de una recepción de bodega ya vinculada, re-calcular discrepancia
        if ($harvest->isWineryReception() && $harvest->wasChanged('total_weight')) {
            $this->resyncLinkedDelivery($harvest);
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
                'error' => $e->getMessage(),
            ]);
        }

        // Si era una recepción de bodega enlazada con una HarvestDelivery, resetearla a pending
        if ($harvest->isWineryReception()) {
            HarvestDelivery::where('harvest_id', $harvest->id)->update([
                'harvest_id' => null,
                'status' => 'pending',
                'discrepancy_kg' => null,
                'dispute_note' => null,
                'dispute_submitted_at' => null,
                'dispute_resolution_note' => null,
                'dispute_resolved_at' => null,
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
     * Re-calcula discrepancia y estado de la HarvestDelivery ya vinculada cuando la bodega
     * corrige el peso de la recepción. Si el nuevo peso elimina la diferencia, pasa a matched;
     * si la introduce, pasa a disputed. El estado resolved no se toca (la disputa ya fue cerrada
     * por ambas partes de forma explícita).
     */
    private function resyncLinkedDelivery(Harvest $harvest): void
    {
        $delivery = HarvestDelivery::where('harvest_id', $harvest->id)
            ->whereIn('status', ['matched', 'disputed'])
            ->first();

        if (! $delivery) {
            return;
        }

        $discrepancyKg = abs((float) $delivery->delivered_kg - (float) $harvest->total_weight);
        $discrepancyPct = (float) $delivery->delivered_kg > 0
            ? ($discrepancyKg / (float) $delivery->delivered_kg) * 100
            : 0;

        $newStatus = $discrepancyPct > 5 ? 'disputed' : 'matched';
        $statusChanged = $newStatus !== $delivery->status;

        if (! $statusChanged && $discrepancyKg == (float) $delivery->discrepancy_kg) {
            return; // Nothing changed
        }

        $delivery->update([
            'status' => $newStatus,
            'discrepancy_kg' => $discrepancyKg,
        ]);

        // If status changed, notify viticulturist
        if ($statusChanged) {
            $viticulturist = $delivery->viticulturist;
            if ($viticulturist?->email) {
                $delivery->load(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'harvest.winery']);
                $notification = $newStatus === 'disputed'
                    ? new HarvestDeliveryAutoDisputedNotification($delivery)
                    : new HarvestDeliveryMatchedNotification($delivery);
                $viticulturist->notify($notification);
            }
        }
    }

    /**
     * Intenta enlazar una recepción de bodega con la HarvestDelivery pendiente del viticultor.
     *
     * Estrategia de matching:
     *  1. Ticket number (harvest_ticket_number = ticket_number) — alta confianza
     *  2. Si hay exactamente UNA entrega pendiente para esa plantación+añada — se enlaza igualmente
     *
     * Estado resultante:
     *  - matched   → discrepancia <= 5%
     *  - disputed  → discrepancia > 5%
     */
    private function linkHarvestDelivery(Harvest $harvest, GrapeReceptionBatch $batch): void
    {
        $baseQuery = HarvestDelivery::where('viticulturist_id', $batch->viticulturist_id)
            ->where('plot_planting_id', $harvest->plot_planting_id)
            ->where('vintage_year', $harvest->vintage)
            ->where('status', 'pending');

        // 1. Buscar por ticket number
        $delivery = null;
        if ($harvest->harvest_ticket_number) {
            $delivery = (clone $baseQuery)
                ->where('ticket_number', $harvest->harvest_ticket_number)
                ->first();
        }

        // 2. Fallback: única entrega pendiente para esa plantación+añada
        if (! $delivery) {
            $pending = $baseQuery->get();
            $delivery = $pending->count() === 1 ? $pending->first() : null;
        }

        if (! $delivery) {
            return;
        }

        $discrepancyKg = abs((float) $delivery->delivered_kg - (float) $harvest->total_weight);
        $discrepancyPct = (float) $delivery->delivered_kg > 0
            ? ($discrepancyKg / (float) $delivery->delivered_kg) * 100
            : 0;

        $newStatus = $discrepancyPct > 5 ? 'disputed' : 'matched';

        $delivery->update([
            'harvest_id' => $harvest->id,
            'status' => $newStatus,
            'discrepancy_kg' => $discrepancyKg,
        ]);

        // Notificar al viticultor del resultado del enlace
        $viticulturist = $delivery->viticulturist;
        if ($viticulturist?->email) {
            $delivery->load(['plotPlanting.grapeVariety', 'plotPlanting.plot', 'harvest.winery']);
            $notification = $newStatus === 'disputed'
                ? new HarvestDeliveryAutoDisputedNotification($delivery)
                : new HarvestDeliveryMatchedNotification($delivery);
            $viticulturist->notify($notification);
        }
    }

    /**
     * Actualiza el rendimiento real en todas las EstimatedYield que coincidan
     * con la plantación y añada de esta cosecha.
     */
    private function syncEstimatedYields(Harvest $harvest): void
    {
        if (! $harvest->plot_planting_id || ! $harvest->vintage) {
            return;
        }

        try {
            EstimatedYield::where('plot_planting_id', $harvest->plot_planting_id)
                ->whereHas('campaign', fn ($q) => $q->where('year', $harvest->vintage))
                ->each(fn (EstimatedYield $ey) => $ey->updateActualYield());
        } catch (\Exception $e) {
            Log::warning('[HarvestObserver] Error al sincronizar rendimiento estimado', [
                'harvest_id' => $harvest->id,
                'plot_planting_id' => $harvest->plot_planting_id,
                'vintage' => $harvest->vintage,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
