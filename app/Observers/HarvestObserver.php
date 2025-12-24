<?php

namespace App\Observers;

use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use App\Models\ContainerHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HarvestObserver
{
    /**
     * Handle the Harvest "created" event.
     * Registra el stock inicial cuando se crea una cosecha y sincroniza used_capacity del contenedor
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

            // Si tiene contenedor, actualizar used_capacity y crear estado/historial
            if ($harvest->container_id) {
                $container = Container::find($harvest->container_id);
                
                if ($container) {
                    // Verificar capacidad disponible
                    if (!$container->hasAvailableCapacity($harvest->total_weight)) {
                        throw new \Exception(
                            "El contenedor '{$container->name}' no tiene capacidad suficiente. " .
                            "Disponible: {$container->getAvailableCapacity()} kg, " .
                            "Requerido: {$harvest->total_weight} kg"
                        );
                    }

                    // Incrementar used_capacity
                    $container->incrementUsedCapacity($harvest->total_weight);

                    // Crear/actualizar estado actual
                    ContainerCurrentState::updateOrCreate(
                        ['container_id' => $container->id],
                        [
                            'harvest_id' => $harvest->id,
                            'current_quantity' => $harvest->total_weight,
                            'has_subproducts' => false,
                        ]
                    );

                    // Registrar en historial
                    ContainerHistory::create([
                        'container_id' => $container->id,
                        'harvest_id' => $harvest->id,
                        'field_activity_id' => $harvest->activity_id,
                        'operation_type' => 'fill',
                        'created_by' => Auth::id(),
                        'quantity' => $harvest->total_weight,
                        'start_date' => now(),
                    ]);
                }
            }

            Log::info('Stock inicial registrado para harvest y contenedor actualizado', [
                'harvest_id' => $harvest->id,
                'quantity' => $harvest->total_weight,
                'container_id' => $harvest->container_id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear stock inicial de harvest', [
                'harvest_id' => $harvest->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
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
     * Al eliminar harvest, liberar capacidad del contenedor
     */
    public function deleting(Harvest $harvest): void
    {
        try {
            if ($harvest->container_id) {
                $container = Container::find($harvest->container_id);
                
                if ($container) {
                    // Decrementar used_capacity
                    $container->decrementUsedCapacity($harvest->total_weight);

                    // Eliminar estado actual si quedó vacío
                    $state = ContainerCurrentState::where('container_id', $container->id)->first();
                    if ($state && $state->harvest_id === $harvest->id) {
                        if ($container->isEmpty()) {
                            $state->delete();
                        } else {
                            // Actualizar cantidad
                            $state->updateQuantity(0);
                        }
                    }

                    // Registrar en historial
                    ContainerHistory::create([
                        'container_id' => $container->id,
                        'harvest_id' => $harvest->id,
                        'operation_type' => 'empty',
                        'created_by' => Auth::id(),
                        'quantity' => -$harvest->total_weight,
                        'start_date' => now(),
                    ]);
                }
            }

            Log::info('Harvest eliminado, capacidad del contenedor liberada', [
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

        // Actualizar contenedor y used_capacity
        if ($harvest->container_id) {
            $container = Container::find($harvest->container_id);
            if ($container) {
                if ($difference > 0) {
                    // Verificar capacidad antes de incrementar
                    if (!$container->hasAvailableCapacity($difference)) {
                        throw new \Exception(
                            "No hay capacidad suficiente en el contenedor. " .
                            "Disponible: {$container->getAvailableCapacity()} kg, " .
                            "Requerido: {$difference} kg"
                        );
                    }
                    $container->incrementUsedCapacity($difference);
                } else {
                    $container->decrementUsedCapacity(abs($difference));
                }

                // Actualizar estado actual
                $state = ContainerCurrentState::where('container_id', $container->id)->first();
                if ($state) {
                    $state->updateQuantity($newTotal);
                }

                // Registrar en historial
                ContainerHistory::create([
                    'container_id' => $container->id,
                    'harvest_id' => $harvest->id,
                    'operation_type' => 'adjustment',
                    'created_by' => Auth::id(),
                    'quantity' => $difference,
                    'start_date' => now(),
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
            $oldContainer = Container::find($oldContainerId);
            if ($oldContainer) {
                // Decrementar used_capacity
                $oldContainer->decrementUsedCapacity($harvest->total_weight);

                // Actualizar o eliminar estado actual
                $oldState = ContainerCurrentState::where('container_id', $oldContainer->id)->first();
                if ($oldState) {
                    if ($oldState->harvest_id === $harvest->id) {
                        if ($oldContainer->isEmpty()) {
                            $oldState->delete();
                        } else {
                            $oldState->updateQuantity(0);
                        }
                    }
                }

                // Registrar en historial
                ContainerHistory::create([
                    'container_id' => $oldContainer->id,
                    'harvest_id' => $harvest->id,
                    'operation_type' => 'transfer',
                    'created_by' => Auth::id(),
                    'quantity' => -$harvest->total_weight,
                    'start_date' => now(),
                ]);
            }
        }

        // Agregar al nuevo contenedor
        if ($newContainerId) {
            $newContainer = Container::find($newContainerId);
            if ($newContainer) {
                // Verificar capacidad disponible
                if (!$newContainer->hasAvailableCapacity($harvest->total_weight)) {
                    throw new \Exception(
                        "El contenedor '{$newContainer->name}' no tiene capacidad suficiente. " .
                        "Disponible: {$newContainer->getAvailableCapacity()} kg, " .
                        "Requerido: {$harvest->total_weight} kg"
                    );
                }

                // Incrementar used_capacity
                $newContainer->incrementUsedCapacity($harvest->total_weight);

                // Crear/actualizar estado actual
                ContainerCurrentState::updateOrCreate(
                    ['container_id' => $newContainer->id],
                    [
                        'harvest_id' => $harvest->id,
                        'current_quantity' => $harvest->total_weight,
                        'has_subproducts' => false,
                    ]
                );

                // Registrar en historial
                ContainerHistory::create([
                    'container_id' => $newContainer->id,
                    'harvest_id' => $harvest->id,
                    'field_activity_id' => $harvest->activity_id,
                    'operation_type' => 'transfer',
                    'created_by' => Auth::id(),
                    'quantity' => $harvest->total_weight,
                    'start_date' => now(),
                ]);
            }
        }
    }
}
