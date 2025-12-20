<?php

namespace App\Observers;

use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\ContainerState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HarvestObserver
{
    /**
     * Handle the Harvest "created" event.
     * Registra el stock inicial cuando se crea una cosecha
     */
    public function created(Harvest $harvest): void
    {
        try {
            // Crear registro inicial de stock
            HarvestStock::create([
                'harvest_id' => $harvest->id,
                'container_id' => $harvest->container_id,
                'user_id' => $harvest->activity->user_id ?? Auth::id(),
                'movement_type' => 'initial',
                'quantity_change' => $harvest->total_weight,
                'quantity_after' => $harvest->total_weight,
                'available_qty' => $harvest->total_weight,
                'reserved_qty' => 0,
                'sold_qty' => 0,
                'gifted_qty' => 0,
                'lost_qty' => 0,
                'notes' => 'Registro inicial de cosecha',
            ]);

            // Actualizar o crear estado del contenedor
            if ($harvest->container_id) {
                $this->updateContainerState($harvest);
            }

            Log::info('Stock inicial registrado para harvest', [
                'harvest_id' => $harvest->id,
                'quantity' => $harvest->total_weight,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear stock inicial de harvest', [
                'harvest_id' => $harvest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Harvest "updating" event.
     * Maneja cambios en cantidad o contenedor
     */
    public function updating(Harvest $harvest): void
    {
        try {
            $oldWeight = $harvest->getOriginal('total_weight');
            $newWeight = $harvest->total_weight;
            $oldContainerId = $harvest->getOriginal('container_id');
            $newContainerId = $harvest->container_id;

            $weightChanged = $oldWeight != $newWeight;
            $containerChanged = $oldContainerId != $newContainerId;

            // Si cambió el peso, registrar ajuste
            if ($weightChanged) {
                $this->handleWeightChange($harvest, $oldWeight, $newWeight);
            }

            // Si cambió el contenedor, actualizar estados
            if ($containerChanged) {
                $this->handleContainerChange($harvest, $oldContainerId, $newContainerId);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar harvest', [
                'harvest_id' => $harvest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Harvest "deleted" event.
     * Al eliminar harvest, limpiar estado del contenedor
     */
    public function deleting(Harvest $harvest): void
    {
        try {
            // Marcar contenedor como vacío si solo tenía esta cosecha
            if ($harvest->container_id) {
                $containerState = ContainerState::where('container_id', $harvest->container_id)
                    ->where('harvest_id', $harvest->id)
                    ->first();

                if ($containerState) {
                    $containerState->markAsEmpty();
                }
            }

            Log::info('Harvest eliminado, contenedor limpiado', [
                'harvest_id' => $harvest->id,
                'container_id' => $harvest->container_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar harvest', [
                'harvest_id' => $harvest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Manejar cambio de peso
     */
    protected function handleWeightChange(Harvest $harvest, float $oldWeight, float $newWeight): void
    {
        $difference = $newWeight - $oldWeight;
        $lastStock = $harvest->stockMovements()->latest()->first();

        if (!$lastStock) {
            Log::warning('No se encontró stock previo para ajuste', [
                'harvest_id' => $harvest->id,
            ]);
            return;
        }

        // Calcular nuevas cantidades
        // El ajuste afecta solo al disponible (asumiendo que lo vendido/reservado no cambia)
        $newAvailable = max(0, $lastStock->available_qty + $difference);
        $newTotal = $lastStock->quantity_after + $difference;

        HarvestStock::create([
            'harvest_id' => $harvest->id,
            'container_id' => $harvest->container_id,
            'user_id' => Auth::id(),
            'movement_type' => 'adjustment',
            'quantity_change' => $difference,
            'quantity_after' => $newTotal,
            'available_qty' => $newAvailable,
            'reserved_qty' => $lastStock->reserved_qty,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf(
                'Ajuste de peso: %s kg → %s kg (%+.3f kg)',
                $oldWeight,
                $newWeight,
                $difference
            ),
        ]);

        // Actualizar contenedor
        if ($harvest->container_id) {
            $containerState = ContainerState::where('container_id', $harvest->container_id)->first();
            if ($containerState) {
                $containerState->update([
                    'total_quantity' => $containerState->total_quantity + $difference,
                    'available_qty' => $containerState->available_qty + $difference,
                    'last_movement_at' => now(),
                    'last_movement_by' => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Manejar cambio de contenedor
     */
    protected function handleContainerChange(Harvest $harvest, ?int $oldContainerId, ?int $newContainerId): void
    {
        // Remover del contenedor antiguo
        if ($oldContainerId) {
            $oldState = ContainerState::where('container_id', $oldContainerId)->first();
            if ($oldState) {
                $newTotal = $oldState->total_quantity - $harvest->total_weight;
                
                if ($newTotal <= 0) {
                    $oldState->markAsEmpty();
                } else {
                    $oldState->update([
                        'total_quantity' => $newTotal,
                        'available_qty' => max(0, $oldState->available_qty - $harvest->total_weight),
                        'last_movement_at' => now(),
                        'last_movement_by' => Auth::id(),
                    ]);
                }
            }
        }

        // Agregar al nuevo contenedor
        if ($newContainerId) {
            $this->updateContainerState($harvest);
        }
    }

    /**
     * Actualizar o crear estado del contenedor
     */
    protected function updateContainerState(Harvest $harvest): void
    {
        $stock = $harvest->getCurrentStock();

        ContainerState::updateOrCreate(
            ['container_id' => $harvest->container_id],
            [
                'content_type' => 'harvest',
                'harvest_id' => $harvest->id,
                'total_quantity' => $stock['total'],
                'available_qty' => $stock['available'],
                'reserved_qty' => $stock['reserved'],
                'sold_qty' => $stock['sold'],
                'last_movement_at' => now(),
                'last_movement_by' => Auth::id(),
            ]
        );
    }
}
