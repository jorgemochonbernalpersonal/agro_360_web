<?php

namespace App\Services;

use App\Models\Container;
use App\Models\ContainerCurrentState;
use App\Models\ContainerHistory;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ContainerStockService
 *
 * Fuente de verdad para todos los movimientos de stock de cosecha/contenedor.
 *
 * Reglas semánticas de Container.used_capacity y ContainerCurrentState.current_quantity:
 *   - Crear cosecha      → sube (físico)
 *   - Ajustar peso       → sube/baja (físico)
 *   - Eliminar cosecha   → baja (físico)
 *   - Reservar (draft)   → SIN cambio (las uvas siguen en el contenedor)
 *   - Confirmar venta    → baja (las uvas salen físicamente)
 *   - Revertir venta     → sube (las uvas vuelven)
 *   - Cancelar draft     → SIN cambio
 *   - Cancelar sent      → sube (las uvas vuelven)
 */
class ContainerStockService
{
    // -------------------------------------------------------------------------
    // Operaciones de cosecha
    // -------------------------------------------------------------------------

    /**
     * Registra el stock inicial al crear una cosecha y actualiza el contenedor.
     */
    public function initializeStock(Harvest $harvest): void
    {
        DB::transaction(function () use ($harvest) {
            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => $harvest->activity?->user_id ?? Auth::id(),
                'movement_type'   => 'initial',
                'quantity_change' => $harvest->total_weight,
                'quantity_after'  => $harvest->total_weight,
                'available_qty'   => $harvest->total_weight,
                'reserved_qty'    => 0,
                'sold_qty'        => 0,
                'gifted_qty'      => 0,
                'lost_qty'        => 0,
                'notes'           => __('Registro inicial de cosecha'),
            ]);

            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    if (! $container->hasAvailableCapacity($harvest->total_weight)) {
                        throw new \Exception(
                            "El contenedor '{$container->name}' no tiene capacidad suficiente. " .
                            "Disponible: {$container->getAvailableCapacity()} kg, " .
                            "Requerido: {$harvest->total_weight} kg"
                        );
                    }

                    $container->incrementUsedCapacity($harvest->total_weight);

                    ContainerCurrentState::updateOrCreate(
                        ['container_id' => $container->id, 'harvest_id' => $harvest->id],
                        [
                            'current_quantity' => $harvest->total_weight,
                            'available_qty'    => $harvest->total_weight,
                            'reserved_qty'     => 0,
                            'sold_qty'         => 0,
                            'has_subproducts'  => false,
                        ]
                    );

                    $this->recordHistory($container, $harvest, 'fill', $harvest->total_weight);
                }
            }

            Log::info('[ContainerStockService] Stock inicial registrado', [
                'harvest_id'   => $harvest->id,
                'total_weight' => $harvest->total_weight,
                'container_id' => $harvest->container_id,
            ]);
        });
    }

    /**
     * Ajusta el stock cuando cambia el peso total de la cosecha.
     * Afecta únicamente al available_qty (lo vendido/reservado no se toca).
     *
     * @throws \Exception Si el contenedor no tiene capacidad para el incremento.
     */
    public function adjustWeight(Harvest $harvest, float $oldWeight, float $newWeight): void
    {
        DB::transaction(function () use ($harvest, $oldWeight, $newWeight) {
            $difference = $newWeight - $oldWeight;
            $lastStock  = $this->getLatestStock($harvest);

            if (! $lastStock) {
                Log::warning('[ContainerStockService] No hay stock previo para ajuste', [
                    'harvest_id' => $harvest->id,
                ]);
                return;
            }

            $newAvailable = max(0, $lastStock->available_qty + $difference);
            $newTotal     = $lastStock->quantity_after + $difference;

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id(),
                'movement_type'   => 'adjustment',
                'quantity_change' => $difference,
                'quantity_after'  => $newTotal,
                'available_qty'   => $newAvailable,
                'reserved_qty'    => $lastStock->reserved_qty,
                'sold_qty'        => $lastStock->sold_qty,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => sprintf(
                    'Ajuste de peso: %s kg → %s kg (%+.3f kg)',
                    $oldWeight,
                    $newWeight,
                    $difference
                ),
            ]);

            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    if ($difference > 0) {
                        if (! $container->hasAvailableCapacity($difference)) {
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

                    // Actualizar current_quantity en el estado del contenedor para esta cosecha
                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($state) {
                        $state->update([
                            'current_quantity' => $newTotal,
                            'available_qty'    => $newAvailable,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }

                    $this->recordHistory($container, $harvest, 'adjustment', $difference);
                }
            }

            Log::info('[ContainerStockService] Peso ajustado', [
                'harvest_id' => $harvest->id,
                'old_weight' => $oldWeight,
                'new_weight' => $newWeight,
                'difference' => $difference,
            ]);
        });
    }

    /**
     * Transfiere la cosecha de un contenedor a otro.
     *
     * @throws \Exception Si el contenedor destino no tiene capacidad.
     */
    public function transferContainer(Harvest $harvest, ?int $oldContainerId, ?int $newContainerId): void
    {
        DB::transaction(function () use ($harvest, $oldContainerId, $newContainerId) {
            // Liberar del contenedor antiguo
            if ($oldContainerId) {
                $oldContainer = Container::lockForUpdate()->find($oldContainerId);
                if ($oldContainer) {
                    $oldContainer->decrementUsedCapacity($harvest->total_weight);

                    $oldState = ContainerCurrentState::where('container_id', $oldContainer->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($oldState) {
                        if ($oldContainer->isEmpty()) {
                            $oldState->delete();
                        } else {
                            $oldState->update([
                                'current_quantity' => 0,
                                'available_qty'    => 0,
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                    }

                    $this->recordHistory($oldContainer, $harvest, 'transfer', -$harvest->total_weight);
                }
            }

            // Asignar al nuevo contenedor
            if ($newContainerId) {
                $newContainer = Container::lockForUpdate()->find($newContainerId);
                if ($newContainer) {
                    if (! $newContainer->hasAvailableCapacity($harvest->total_weight)) {
                        throw new \Exception(
                            "El contenedor '{$newContainer->name}' no tiene capacidad suficiente. " .
                            "Disponible: {$newContainer->getAvailableCapacity()} kg, " .
                            "Requerido: {$harvest->total_weight} kg"
                        );
                    }

                    $newContainer->incrementUsedCapacity($harvest->total_weight);

                    // Obtener valores de stock actuales para propagar al nuevo estado
                    $lastStock = $harvest->stockMovements()->latest()->first();
                    $available = $lastStock ? $lastStock->available_qty : $harvest->total_weight;
                    $reserved  = $lastStock ? $lastStock->reserved_qty : 0;
                    $sold      = $lastStock ? $lastStock->sold_qty : 0;

                    ContainerCurrentState::updateOrCreate(
                        ['container_id' => $newContainer->id, 'harvest_id' => $harvest->id],
                        [
                            'current_quantity' => $harvest->total_weight,
                            'available_qty'    => $available,
                            'reserved_qty'     => $reserved,
                            'sold_qty'         => $sold,
                            'has_subproducts'  => false,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]
                    );

                    $this->recordHistory($newContainer, $harvest, 'transfer', $harvest->total_weight);
                }
            }

            Log::info('[ContainerStockService] Contenedor transferido', [
                'harvest_id'       => $harvest->id,
                'old_container_id' => $oldContainerId,
                'new_container_id' => $newContainerId,
            ]);
        });
    }

    /**
     * Ajusta el peso Y cambia de contenedor en una sola operación atómica.
     * Libera el contenedor antiguo con el PESO VIEJO e ingresa el PESO NUEVO al contenedor destino.
     *
     * @throws \Exception Si el contenedor destino no tiene capacidad.
     */
    public function adjustAndTransfer(Harvest $harvest, float $oldWeight, float $newWeight, ?int $oldContainerId, ?int $newContainerId): void
    {
        DB::transaction(function () use ($harvest, $oldWeight, $newWeight, $oldContainerId, $newContainerId) {
            $difference = $newWeight - $oldWeight;
            $lastStock  = $this->getLatestStock($harvest);

            if ($lastStock) {
                $newAvailable = max(0, $lastStock->available_qty + $difference);
                $newTotal     = $lastStock->quantity_after + $difference;

                HarvestStock::create([
                    'harvest_id'      => $harvest->id,
                    'container_id'    => $newContainerId,
                    'user_id'         => Auth::id(),
                    'movement_type'   => 'adjustment',
                    'quantity_change' => $difference,
                    'quantity_after'  => $newTotal,
                    'available_qty'   => $newAvailable,
                    'reserved_qty'    => $lastStock->reserved_qty,
                    'sold_qty'        => $lastStock->sold_qty,
                    'gifted_qty'      => $lastStock->gifted_qty,
                    'lost_qty'        => $lastStock->lost_qty,
                    'notes'           => sprintf(
                        'Ajuste de peso y cambio de contenedor: %s → %s kg',
                        $oldWeight,
                        $newWeight
                    ),
                ]);
            }

            // Liberar contenedor antiguo con el PESO VIEJO
            if ($oldContainerId) {
                $oldContainer = Container::lockForUpdate()->find($oldContainerId);
                if ($oldContainer) {
                    $oldContainer->decrementUsedCapacity($oldWeight);

                    $oldState = ContainerCurrentState::where('container_id', $oldContainer->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($oldState) {
                        if ($oldContainer->isEmpty()) {
                            $oldState->delete();
                        } else {
                            $oldState->update([
                                'current_quantity' => 0,
                                'available_qty'    => 0,
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                    }

                    $this->recordHistory($oldContainer, $harvest, 'transfer', -$oldWeight);
                }
            }

            // Asignar contenedor nuevo con el PESO NUEVO
            if ($newContainerId) {
                $newContainer = Container::lockForUpdate()->find($newContainerId);
                if ($newContainer) {
                    if (! $newContainer->hasAvailableCapacity($newWeight)) {
                        throw new \Exception(
                            "El contenedor '{$newContainer->name}' no tiene capacidad suficiente. " .
                            "Disponible: {$newContainer->getAvailableCapacity()} kg, " .
                            "Requerido: {$newWeight} kg"
                        );
                    }

                    $newContainer->incrementUsedCapacity($newWeight);

                    $available = $lastStock ? max(0, $lastStock->available_qty + $difference) : $newWeight;
                    ContainerCurrentState::updateOrCreate(
                        ['container_id' => $newContainer->id, 'harvest_id' => $harvest->id],
                        [
                            'current_quantity' => $newWeight,
                            'available_qty'    => $available,
                            'reserved_qty'     => $lastStock?->reserved_qty ?? 0,
                            'sold_qty'         => $lastStock?->sold_qty ?? 0,
                            'has_subproducts'  => false,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]
                    );

                    $this->recordHistory($newContainer, $harvest, 'transfer', $newWeight);
                }
            }

            Log::info('[ContainerStockService] Peso ajustado y contenedor transferido', [
                'harvest_id'       => $harvest->id,
                'old_weight'       => $oldWeight,
                'new_weight'       => $newWeight,
                'old_container_id' => $oldContainerId,
                'new_container_id' => $newContainerId,
            ]);
        });
    }

    /**
     * Libera la capacidad del contenedor al eliminar una cosecha.
     */
    public function releaseHarvestStock(Harvest $harvest): void
    {
        DB::transaction(function () use ($harvest) {
            if (! $harvest->container_id) {
                return;
            }

            $container = Container::lockForUpdate()->find($harvest->container_id);
            if (! $container) {
                return;
            }

            $container->decrementUsedCapacity($harvest->total_weight);

            $state = ContainerCurrentState::where('container_id', $container->id)
                ->where('harvest_id', $harvest->id)
                ->first();
            if ($state) {
                if ($container->isEmpty()) {
                    $state->delete();
                } else {
                    $state->update([
                        'current_quantity' => 0,
                        'available_qty'    => 0,
                        'last_movement_at' => now(),
                        'last_movement_by' => Auth::id(),
                    ]);
                }
            }

            $this->recordHistory($container, $harvest, 'empty', -$harvest->total_weight);

            Log::info('[ContainerStockService] Stock de cosecha liberado al eliminar', [
                'harvest_id'   => $harvest->id,
                'container_id' => $harvest->container_id,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Operaciones de facturación
    // -------------------------------------------------------------------------

    /**
     * Reserva stock para un item de factura en estado draft.
     * NO cambia used_capacity ni current_quantity (las uvas siguen físicamente).
     *
     * @throws \Exception Si no hay stock disponible suficiente.
     */
    public function reserveStock(Harvest $harvest, InvoiceItem $item): void
    {
        DB::transaction(function () use ($harvest, $item) {
            $lastStock = $this->ensureInitialStock($harvest, $item->invoice->user_id ?? null);

            if ($lastStock->available_qty < $item->quantity) {
                throw new \RuntimeException(
                    "Stock insuficiente para cosecha #{$harvest->id}: " .
                    "disponible {$lastStock->available_qty} kg, solicitado {$item->quantity} kg."
                );
            }

            $newAvailable = $lastStock->available_qty - $item->quantity;
            $newReserved  = $lastStock->reserved_qty + $item->quantity;

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id() ?? $item->invoice->user_id,
                'invoice_item_id' => $item->id,
                'movement_type'   => 'reserve',
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $newAvailable,
                'reserved_qty'    => $newReserved,
                'sold_qty'        => $lastStock->sold_qty,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => sprintf(
                    'Stock reservado - Item #%d en Factura %s',
                    $item->id,
                    $item->invoice->delivery_note_code ?? $item->invoice_id
                ),
                'reference_number' => $item->invoice->delivery_note_code,
            ]);

            // Solo actualiza los sub-campos de stock, NO current_quantity ni used_capacity
            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $state = $this->getOrCreateContainerState($container, $harvest);
                    $state->update([
                        'available_qty'    => max(0, ($state->available_qty ?? 0) - $item->quantity),
                        'reserved_qty'     => ($state->reserved_qty ?? 0) + $item->quantity,
                        'last_movement_at' => now(),
                        'last_movement_by' => Auth::id() ?? $item->invoice->user_id,
                    ]);
                }
            }

            Log::info('[ContainerStockService] Stock reservado', [
                'harvest_id'      => $harvest->id,
                'item_id'         => $item->id,
                'quantity'        => $item->quantity,
                'new_available'   => $newAvailable,
                'new_reserved'    => $newReserved,
            ]);
        });
    }

    /**
     * Libera una reserva al eliminar un item de factura draft.
     * NO cambia used_capacity ni current_quantity.
     */
    public function unreserveStock(Harvest $harvest, InvoiceItem $item): void
    {
        DB::transaction(function () use ($harvest, $item) {
            $lastStock = $this->getLatestStock($harvest);
            if (! $lastStock) {
                return;
            }

            $newAvailable = $lastStock->available_qty + $item->quantity;
            $newReserved  = max(0, $lastStock->reserved_qty - $item->quantity);

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id() ?? $item->invoice->user_id,
                'invoice_item_id' => $item->id,
                'movement_type'   => 'unreserve',
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $newAvailable,
                'reserved_qty'    => $newReserved,
                'sold_qty'        => $lastStock->sold_qty,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => sprintf(
                    'Reserva liberada - Item #%d eliminado de Factura %s',
                    $item->id,
                    $item->invoice->delivery_note_code ?? $item->invoice_id
                ),
                'reference_number' => $item->invoice->delivery_note_code,
            ]);

            // Solo actualiza sub-campos, NO current_quantity ni used_capacity
            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($state) {
                        $state->update([
                            'available_qty'    => ($state->available_qty ?? 0) + $item->quantity,
                            'reserved_qty'     => max(0, ($state->reserved_qty ?? 0) - $item->quantity),
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }
                }
            }

            Log::info('[ContainerStockService] Reserva liberada', [
                'harvest_id' => $harvest->id,
                'item_id'    => $item->id,
                'quantity'   => $item->quantity,
            ]);
        });
    }

    /**
     * Confirma una venta: convierte reserva → vendido.
     * Las uvas salen físicamente → decrements used_capacity y current_quantity.
     */
    public function confirmSale(Harvest $harvest, InvoiceItem $item, string $invoiceRef = ''): void
    {
        DB::transaction(function () use ($harvest, $item, $invoiceRef) {
            $lastStock = $this->getLatestStock($harvest);
            if (! $lastStock) {
                return;
            }

            // Solo confirmar si hay cantidad reservada suficiente
            if ($lastStock->reserved_qty < $item->quantity) {
                Log::warning('[ContainerStockService] No hay suficiente stock reservado para confirmar venta', [
                    'harvest_id' => $harvest->id,
                    'item_id'    => $item->id,
                    'reserved'   => $lastStock->reserved_qty,
                    'required'   => $item->quantity,
                ]);
                return;
            }

            $newReserved = $lastStock->reserved_qty - $item->quantity;
            $newSold     = $lastStock->sold_qty + $item->quantity;

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id(),
                'invoice_item_id' => $item->id,
                'movement_type'   => 'sale',
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $lastStock->available_qty,
                'reserved_qty'    => $newReserved,
                'sold_qty'        => $newSold,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => "Venta confirmada - Factura #{$invoiceRef} aprobada",
                'reference_number' => $invoiceRef,
            ]);

            // Salida física: baja used_capacity y current_quantity
            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $container->decrementUsedCapacity($item->quantity);

                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($state) {
                        $state->update([
                            'current_quantity' => max(0, $state->current_quantity - $item->quantity),
                            'reserved_qty'     => max(0, ($state->reserved_qty ?? 0) - $item->quantity),
                            'sold_qty'         => ($state->sold_qty ?? 0) + $item->quantity,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }

                    $this->recordHistory($container, $harvest, 'sale', -$item->quantity);
                }
            }

            Log::info('[ContainerStockService] Venta confirmada', [
                'harvest_id'  => $harvest->id,
                'item_id'     => $item->id,
                'quantity'    => $item->quantity,
                'invoice_ref' => $invoiceRef,
            ]);
        });
    }

    /**
     * Revierte una venta confirmada a estado reservado (sent → draft).
     * Las uvas vuelven físicamente → increments used_capacity y current_quantity.
     */
    public function revertSaleToReservation(Harvest $harvest, InvoiceItem $item): void
    {
        DB::transaction(function () use ($harvest, $item) {
            $lastStock = $this->getLatestStock($harvest);
            if (! $lastStock) {
                return;
            }

            $newReserved = $lastStock->reserved_qty + $item->quantity;
            $newSold     = max(0, $lastStock->sold_qty - $item->quantity);

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id(),
                'invoice_item_id' => $item->id,
                'movement_type'   => 'reserve',
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $lastStock->available_qty,
                'reserved_qty'    => $newReserved,
                'sold_qty'        => $newSold,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => __('Venta revertida a reserva - Factura vuelta a borrador'),
            ]);

            // Vuelta física: sube used_capacity y current_quantity
            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $container->incrementUsedCapacity($item->quantity);

                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($state) {
                        $state->update([
                            'current_quantity' => $state->current_quantity + $item->quantity,
                            'reserved_qty'     => ($state->reserved_qty ?? 0) + $item->quantity,
                            'sold_qty'         => max(0, ($state->sold_qty ?? 0) - $item->quantity),
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }

                    $this->recordHistory($container, $harvest, 'adjustment', $item->quantity);
                }
            }

            Log::info('[ContainerStockService] Venta revertida a reserva', [
                'harvest_id' => $harvest->id,
                'item_id'    => $item->id,
                'quantity'   => $item->quantity,
            ]);
        });
    }

    /**
     * Libera completamente el stock de un item al cancelar/eliminar una factura.
     *
     * @param string $fromStatus Estado anterior de la factura ('draft' o cualquier otro).
     *                           Si era 'draft', la cantidad estaba reservada.
     *                           Si era otro estado (sent/approved), la cantidad estaba vendida.
     */
    public function releaseFromInvoice(Harvest $harvest, InvoiceItem $item, string $fromStatus): void
    {
        DB::transaction(function () use ($harvest, $item, $fromStatus) {
            $lastStock = $this->getLatestStock($harvest);
            if (! $lastStock) {
                return;
            }

            $wasDraft    = ($fromStatus === 'draft');
            $movementType = $wasDraft ? 'unreserve' : 'return';

            $newAvailable = $lastStock->available_qty + $item->quantity;
            $newReserved  = $wasDraft ? max(0, $lastStock->reserved_qty - $item->quantity) : $lastStock->reserved_qty;
            $newSold      = $wasDraft ? $lastStock->sold_qty : max(0, $lastStock->sold_qty - $item->quantity);

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id(),
                'invoice_item_id' => $item->id,
                'movement_type'   => $movementType,
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $newAvailable,
                'reserved_qty'    => $newReserved,
                'sold_qty'        => $newSold,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => sprintf(
                    'Stock liberado - Factura cancelada (estado previo: %s)',
                    $fromStatus
                ),
            ]);

            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();

                    if ($wasDraft) {
                        // Las uvas nunca salieron → solo restaurar sub-campos
                        if ($state) {
                            $state->update([
                                'available_qty'    => ($state->available_qty ?? 0) + $item->quantity,
                                'reserved_qty'     => max(0, ($state->reserved_qty ?? 0) - $item->quantity),
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                    } else {
                        // Las uvas habían salido → restaurar físicamente
                        $container->incrementUsedCapacity($item->quantity);
                        if ($state) {
                            $state->update([
                                'current_quantity' => $state->current_quantity + $item->quantity,
                                'available_qty'    => ($state->available_qty ?? 0) + $item->quantity,
                                'sold_qty'         => max(0, ($state->sold_qty ?? 0) - $item->quantity),
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                        $this->recordHistory($container, $harvest, 'adjustment', $item->quantity);
                    }
                }
            }

            Log::info('[ContainerStockService] Stock liberado por cancelación', [
                'harvest_id'  => $harvest->id,
                'item_id'     => $item->id,
                'quantity'    => $item->quantity,
                'from_status' => $fromStatus,
                'was_draft'   => $wasDraft,
            ]);
        });
    }

    /**
     * Venta directa: añadir un item a una factura ya enviada/aprobada.
     * No hay reserva previa — descuenta directamente del disponible.
     */
    public function directSale(Harvest $harvest, InvoiceItem $item, string $invoiceRef = ''): void
    {
        DB::transaction(function () use ($harvest, $item, $invoiceRef) {
            $lastStock = $this->ensureInitialStock($harvest, $item->invoice->user_id ?? null);

            if ($lastStock->available_qty < $item->quantity) {
                throw new \RuntimeException(
                    "Stock insuficiente para venta directa de cosecha #{$harvest->id}: " .
                    "disponible {$lastStock->available_qty} kg, solicitado {$item->quantity} kg."
                );
            }

            $newAvailable = $lastStock->available_qty - $item->quantity;
            $newSold      = $lastStock->sold_qty + $item->quantity;

            HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id() ?? $item->invoice->user_id,
                'invoice_item_id' => $item->id,
                'movement_type'   => 'sale',
                'quantity_change' => 0,
                'quantity_after'  => $lastStock->quantity_after,
                'available_qty'   => $newAvailable,
                'reserved_qty'    => $lastStock->reserved_qty,
                'sold_qty'        => $newSold,
                'gifted_qty'      => $lastStock->gifted_qty,
                'lost_qty'        => $lastStock->lost_qty,
                'notes'           => sprintf(
                    'Venta directa - Item #%d añadido a Factura %s',
                    $item->id,
                    $invoiceRef
                ),
                'reference_number' => $invoiceRef,
            ]);

            if ($harvest->container_id) {
                $container = Container::lockForUpdate()->find($harvest->container_id);
                if ($container) {
                    $container->decrementUsedCapacity($item->quantity);

                    $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                    if ($state) {
                        $state->update([
                            'current_quantity' => max(0, $state->current_quantity - $item->quantity),
                            'available_qty'    => ($state->available_qty ?? 0) - $item->quantity,
                            'sold_qty'         => ($state->sold_qty ?? 0) + $item->quantity,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }

                    $this->recordHistory($container, $harvest, 'sale', -$item->quantity);
                }
            }

            Log::info('[ContainerStockService] Venta directa registrada', [
                'harvest_id'  => $harvest->id,
                'item_id'     => $item->id,
                'quantity'    => $item->quantity,
                'invoice_ref' => $invoiceRef,
            ]);
        });
    }

    /**
     * Ajusta el stock cuando cambia la cantidad de un InvoiceItem existente.
     *
     * @param string $invoiceStatus Estado actual de la factura ('draft', 'sent', 'approved', etc.)
     */
    public function adjustItemQuantity(Harvest $harvest, InvoiceItem $item, float $oldQty, float $newQty, string $invoiceStatus): void
    {
        $diff = $newQty - $oldQty;
        if ($diff == 0) {
            return;
        }

        DB::transaction(function () use ($harvest, $item, $oldQty, $newQty, $diff, $invoiceStatus) {
            $lastStock = $this->getLatestStock($harvest);
            if (! $lastStock) {
                return;
            }

            if ($invoiceStatus === 'draft') {
                // Ajuste de reserva
                if ($diff > 0 && $lastStock->available_qty < $diff) {
                    throw new \RuntimeException(
                        "Stock insuficiente para ajustar reserva de cosecha #{$harvest->id}: " .
                        "disponible {$lastStock->available_qty} kg, incremento {$diff} kg."
                    );
                }
                $newReserved  = $lastStock->reserved_qty + $diff;
                $newAvailable = $lastStock->available_qty - $diff;

                HarvestStock::create([
                    'harvest_id'      => $harvest->id,
                    'container_id'    => $harvest->container_id,
                    'user_id'         => Auth::id(),
                    'invoice_item_id' => $item->id,
                    'movement_type'   => 'reserve',
                    'quantity_change' => 0,
                    'quantity_after'  => $lastStock->quantity_after,
                    'available_qty'   => $newAvailable,
                    'reserved_qty'    => max(0, $newReserved),
                    'sold_qty'        => $lastStock->sold_qty,
                    'gifted_qty'      => $lastStock->gifted_qty,
                    'lost_qty'        => $lastStock->lost_qty,
                    'notes'           => sprintf('Ajuste de reserva: %.3f → %.3f kg', $oldQty, $newQty),
                ]);

                // La reserva no cambia used_capacity
                if ($harvest->container_id) {
                    $container = Container::lockForUpdate()->find($harvest->container_id);
                    if ($container) {
                        $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                        if ($state) {
                            $state->update([
                                'available_qty'    => ($state->available_qty ?? 0) - $diff,
                                'reserved_qty'     => max(0, ($state->reserved_qty ?? 0) + $diff),
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                    }
                }
            } else {
                // Ajuste de venta (sent, approved, etc.)
                $newSold      = $lastStock->sold_qty + $diff;
                $newAvailable = $lastStock->available_qty - $diff;

                HarvestStock::create([
                    'harvest_id'      => $harvest->id,
                    'container_id'    => $harvest->container_id,
                    'user_id'         => Auth::id(),
                    'invoice_item_id' => $item->id,
                    'movement_type'   => $diff > 0 ? 'sale' : 'return',
                    'quantity_change' => 0,
                    'quantity_after'  => $lastStock->quantity_after,
                    'available_qty'   => $newAvailable,
                    'reserved_qty'    => $lastStock->reserved_qty,
                    'sold_qty'        => max(0, $newSold),
                    'gifted_qty'      => $lastStock->gifted_qty,
                    'lost_qty'        => $lastStock->lost_qty,
                    'notes'           => sprintf('Ajuste de venta: %.3f → %.3f kg', $oldQty, $newQty),
                ]);

                // Ajuste físico en el contenedor
                if ($harvest->container_id) {
                    $container = Container::lockForUpdate()->find($harvest->container_id);
                    if ($container) {
                        if ($diff > 0) {
                            $container->decrementUsedCapacity($diff);
                        } else {
                            $container->incrementUsedCapacity(abs($diff));
                        }

                        $state = ContainerCurrentState::where('container_id', $container->id)
                        ->where('harvest_id', $harvest->id)
                        ->first();
                        if ($state) {
                            $state->update([
                                'current_quantity' => max(0, $state->current_quantity - $diff),
                                'available_qty'    => ($state->available_qty ?? 0) - $diff,
                                'sold_qty'         => max(0, ($state->sold_qty ?? 0) + $diff),
                                'last_movement_at' => now(),
                                'last_movement_by' => Auth::id(),
                            ]);
                        }
                    }
                }
            }

            Log::info('[ContainerStockService] Cantidad de item ajustada', [
                'harvest_id'     => $harvest->id,
                'item_id'        => $item->id,
                'old_qty'        => $oldQty,
                'new_qty'        => $newQty,
                'invoice_status' => $invoiceStatus,
            ]);
        });
    }

    // -------------------------------------------------------------------------
    // Métodos privados de soporte
    // -------------------------------------------------------------------------

    /**
     * Obtiene el último movimiento de stock con lock pesimista.
     */
    private function getLatestStock(Harvest $harvest): ?HarvestStock
    {
        return HarvestStock::where('harvest_id', $harvest->id)
            ->lockForUpdate()
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Obtiene el último stock o crea el registro inicial si no existe.
     */
    private function ensureInitialStock(Harvest $harvest, ?int $userId = null): HarvestStock
    {
        // Bloquear la cosecha para prevenir race conditions
        $harvest = Harvest::lockForUpdate()->find($harvest->id);
        $lastStock = $this->getLatestStock($harvest);

        if (! $lastStock) {
            $lastStock = HarvestStock::create([
                'harvest_id'      => $harvest->id,
                'container_id'    => $harvest->container_id,
                'user_id'         => Auth::id() ?? $userId,
                'movement_type'   => 'initial',
                'quantity_change' => $harvest->total_weight,
                'quantity_after'  => $harvest->total_weight,
                'available_qty'   => $harvest->total_weight,
                'reserved_qty'    => 0,
                'sold_qty'        => 0,
                'gifted_qty'      => 0,
                'lost_qty'        => 0,
                'notes'           => __('Stock inicial de cosecha (auto-creado)'),
            ]);

            Log::info('[ContainerStockService] Stock inicial auto-creado', [
                'harvest_id'   => $harvest->id,
                'total_weight' => $harvest->total_weight,
            ]);
        }

        return $lastStock;
    }

    /**
     * Obtiene o crea el ContainerCurrentState para un contenedor/cosecha.
     */
    private function getOrCreateContainerState(Container $container, Harvest $harvest): ContainerCurrentState
    {
        return ContainerCurrentState::firstOrCreate(
            ['container_id' => $container->id, 'harvest_id' => $harvest->id],
            [
                'current_quantity' => $container->used_capacity,
                'available_qty'    => $container->used_capacity,
                'reserved_qty'     => 0,
                'sold_qty'         => 0,
                'has_subproducts'  => false,
            ]
        );
    }

    /**
     * Registra una entrada en el historial del contenedor.
     * Quantity positiva = entrada, negativa = salida.
     */
    private function recordHistory(Container $container, Harvest $harvest, string $operationType, float $quantity): void
    {
        ContainerHistory::create([
            'container_id'     => $container->id,
            'harvest_id'       => $harvest->id,
            'field_activity_id' => $harvest->activity_id,
            'operation_type'   => $operationType,
            'created_by'       => Auth::id(),
            'quantity'         => $quantity,
            'start_date'       => now(),
        ]);
    }
}
