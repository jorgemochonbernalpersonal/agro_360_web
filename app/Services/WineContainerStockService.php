<?php

namespace App\Services;

use App\Models\Container;
use App\Models\ContainerCurrentState;
use App\Models\ContainerHistory;
use App\Models\WineBottling;
use App\Models\WineContainerStockEntry;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * WineContainerStockService
 *
 * Fuente de verdad para movimientos de vino elaborado entre contenedores.
 * Paralelo al ContainerStockService que gestiona cosecha/uva.
 *
 * Reglas:
 *   - Registrar trasvase → decrementa wine_volume_liters en origen, incrementa en destino
 *   - Revertir trasvase  → operación inversa
 *   - Actualizar trasvase → revertir viejo + aplicar nuevo (atómico)
 *   - Actualiza ContainerCurrentState.wine_id en el contenedor destino
 *   - Registra audit trail en ContainerHistory
 */
class WineContainerStockService
{
    /**
     * Registra un trasvase nuevo: mueve litros de origen → destino.
     */
    public function recordTransfer(WineTransfer $transfer): void
    {
        if (! $transfer->to_container_id) {
            return;
        }

        DB::transaction(function () use ($transfer) {
            $qty = (float) $transfer->quantity;

            // Bloquear contenedores para evitar race conditions
            $containerIds = array_filter([
                $transfer->from_container_id,
                $transfer->to_container_id,
            ]);
            Container::whereIn('id', $containerIds)->lockForUpdate()->get();

            // ── Origen: decrementar ─────────────────────────────────────────
            if ($transfer->from_container_id) {
                $fromContainer = Container::find($transfer->from_container_id);
                if ($fromContainer) {
                    if ((float) $fromContainer->wine_volume_liters < $qty) {
                        throw new \RuntimeException(
                            "El contenedor «{$fromContainer->name}» no tiene suficiente vino: " .
                            "disponible {$fromContainer->wine_volume_liters} L, solicitado {$qty} L."
                        );
                    }
                    $fromContainer->wine_volume_liters = $fromContainer->wine_volume_liters - $qty;
                    $fromContainer->save();

                    $this->updateCurrentState($fromContainer, null, -$qty);
                    // Para blending, el vino que sale del origen es source_wine_id, no el resultado
                    $this->recordHistory($fromContainer, $transfer, 'wine_transfer_out', -$qty, $transfer->source_wine_id);
                }
            }

            // ── Destino: incrementar ────────────────────────────────────────
            $toContainer = Container::find($transfer->to_container_id);
            if ($toContainer) {
                $toContainer->wine_volume_liters = $toContainer->wine_volume_liters + $qty;
                $toContainer->save();

                $this->updateCurrentState($toContainer, $transfer->wine_id, $qty);
                $this->recordHistory($toContainer, $transfer, 'wine_transfer_in', $qty);
            }

            Log::info('[WineContainerStockService] Trasvase registrado', [
                'transfer_id'      => $transfer->id,
                'wine_id'          => $transfer->wine_id,
                'from_container'   => $transfer->from_container_id,
                'to_container'     => $transfer->to_container_id,
                'quantity'         => $qty,
            ]);
        });
    }

    /**
     * Revierte un trasvase: deshace los cambios en los contenedores.
     * Se llama antes de eliminar o antes de aplicar una edición.
     */
    public function revertTransfer(WineTransfer $transfer): void
    {
        if (! $transfer->to_container_id) {
            return;
        }

        DB::transaction(function () use ($transfer) {
            $qty = (float) $transfer->quantity;

            $containerIds = array_filter([
                $transfer->from_container_id,
                $transfer->to_container_id,
            ]);
            Container::whereIn('id', $containerIds)->lockForUpdate()->get();

            // ── Destino: restar PRIMERO (para que updateCurrentState calcule bien) ─
            $toContainer = Container::find($transfer->to_container_id);
            if ($toContainer) {
                $toContainer->wine_volume_liters = max(0, $toContainer->wine_volume_liters - $qty);
                $toContainer->save();

                $this->updateCurrentState($toContainer, null, -$qty);
                $this->recordHistory($toContainer, $transfer, 'wine_transfer_revert_out', -$qty);
            }

            // ── Origen: restaurar con el wine_id original ────────────────────
            if ($transfer->from_container_id) {
                $fromContainer = Container::find($transfer->from_container_id);
                if ($fromContainer) {
                    $fromContainer->wine_volume_liters = $fromContainer->wine_volume_liters + $qty;
                    $fromContainer->save();

                    // Para blending, el origen tenía source_wine_id, no el wine resultado
                    $originWineId = $transfer->source_wine_id ?? $transfer->wine_id;
                    $this->updateCurrentState($fromContainer, $originWineId, $qty);
                    $this->recordHistory($fromContainer, $transfer, 'wine_transfer_revert_in', $qty, $originWineId);
                }
            }

            Log::info('[WineContainerStockService] Trasvase revertido', [
                'transfer_id' => $transfer->id,
                'quantity'    => $qty,
            ]);
        });
    }

    /**
     * Actualiza un trasvase: revierte el antiguo y aplica el nuevo.
     * Permite cambios en contenedores, cantidad o vino.
     *
     * @param array $oldData  ['wine_id', 'from_container_id', 'to_container_id', 'quantity']
     */
    public function updateTransfer(WineTransfer $transfer, array $oldData): void
    {
        DB::transaction(function () use ($transfer, $oldData) {
            // Revertir estado anterior
            $fake = new WineTransfer($oldData);
            $this->revertTransfer($fake);

            // Aplicar nuevo estado
            $this->recordTransfer($transfer);
        });
    }

    // ─── Vaciado manual ──────────────────────────────────────────────────────

    /**
     * Vacía completamente un contenedor de vino elaborado.
     * No afecta a used_capacity (uva/cosecha) ni al estado de cosecha.
     */
    public function emptyWineContent(Container $container): void
    {
        DB::transaction(function () use ($container) {
            Container::whereKey($container->id)->lockForUpdate()->first();

            $prevLiters = (float) $container->wine_volume_liters;

            if ($prevLiters <= 0) {
                return;
            }

            $container->wine_volume_liters = 0;
            $container->save();

            // Limpiar wine_id del estado actual (locked via container above)
            $state = ContainerCurrentState::lockForUpdate()
                ->firstOrNew(['container_id' => $container->id]);
            $state->wine_id          = null;
            $state->last_movement_at = now();
            $state->last_movement_by = Auth::id();
            $state->save();

            ContainerHistory::create([
                'container_id'   => $container->id,
                'operation_type' => 'empty',
                'quantity'       => -$prevLiters,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);

            Log::info('[WineContainerStockService] Contenedor vaciado', [
                'container_id' => $container->id,
                'liters_removed' => $prevLiters,
            ]);
        });
    }

    /**
     * Ajuste manual de wine_volume_liters (corrección de inventario).
     * Registra la diferencia como 'adjustment' en ContainerHistory.
     */
    public function manualAdjust(Container $container, ?int $wineId, float $newLiters): void
    {
        DB::transaction(function () use ($container, $wineId, $newLiters) {
            Container::whereKey($container->id)->lockForUpdate()->first();

            $prev  = (float) $container->wine_volume_liters;
            $delta = $newLiters - $prev;

            $container->wine_volume_liters = max(0, $newLiters);
            $container->save();

            $this->updateCurrentState($container, $wineId, $delta);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $wineId,
                'operation_type' => 'adjustment',
                'quantity'       => $delta,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);

            Log::info('[WineContainerStockService] Ajuste manual', [
                'container_id' => $container->id,
                'prev_liters'  => $prev,
                'new_liters'   => $newLiters,
                'delta'        => $delta,
            ]);
        });
    }

    // ─── Mermas ──────────────────────────────────────────────────────────────

    /**
     * Registra una merma: descuenta litros del contenedor.
     */
    public function recordLoss(WineLoss $loss): void
    {
        if (! $loss->container_id) {
            return;
        }

        DB::transaction(function () use ($loss) {
            $qty = (float) $loss->quantity;

            $container = Container::lockForUpdate()->find($loss->container_id);
            if (! $container) {
                return;
            }

            if ((float) $container->wine_volume_liters < $qty) {
                throw new \RuntimeException(
                    "El contenedor «{$container->name}» no tiene suficiente vino para la merma: " .
                    "disponible {$container->wine_volume_liters} L, merma {$qty} L."
                );
            }

            $container->wine_volume_liters = $container->wine_volume_liters - $qty;
            $container->save();

            $this->updateCurrentState($container, null, -$qty);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $loss->wine_id,
                'operation_type' => 'wine_loss',
                'quantity'       => -$qty,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);

            Log::info('[WineContainerStockService] Merma registrada', [
                'loss_id'      => $loss->id,
                'container_id' => $loss->container_id,
                'quantity'     => $qty,
            ]);
        });
    }

    /**
     * Revierte una merma: restaura los litros al contenedor.
     */
    public function revertLoss(WineLoss $loss): void
    {
        if (! $loss->container_id) {
            return;
        }

        DB::transaction(function () use ($loss) {
            $qty = (float) $loss->quantity;

            $container = Container::lockForUpdate()->find($loss->container_id);
            if (! $container) {
                return;
            }

            $container->wine_volume_liters += $qty;
            $container->save();

            $this->updateCurrentState($container, $loss->wine_id, $qty);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $loss->wine_id,
                'operation_type' => 'wine_loss_revert',
                'quantity'       => $qty,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);
        });
    }

    /**
     * Actualiza una merma: revierte la anterior y aplica la nueva.
     *
     * @param array $oldData ['wine_id', 'container_id', 'quantity']
     */
    public function updateLoss(WineLoss $loss, array $oldData): void
    {
        DB::transaction(function () use ($loss, $oldData) {
            $fake = new WineLoss($oldData);
            $this->revertLoss($fake);
            $this->recordLoss($loss);
        });
    }

    // ─── Embotellado ─────────────────────────────────────────────────────────

    /**
     * Registra un embotellado: descuenta wine_volume_liters del contenedor origen.
     */
    public function recordBottling(WineBottling $bottling): void
    {
        if (! $bottling->container_id) {
            return;
        }

        DB::transaction(function () use ($bottling) {
            $qty = (float) $bottling->quantity_liters;

            $container = Container::lockForUpdate()->find($bottling->container_id);
            if (! $container) {
                return;
            }

            $container->wine_volume_liters = max(0, $container->wine_volume_liters - $qty);
            $container->save();

            $this->updateCurrentState($container, null, -$qty);

            ContainerHistory::create([
                'container_id'           => $container->id,
                'wine_id'                => $bottling->wine_id,
                'wine_process_detail_id' => $bottling->wine_process_detail_id,
                'operation_type'         => 'bottling',
                'quantity'               => -$qty,
                'created_by'             => Auth::id(),
                'start_date'             => $bottling->bottling_date ?? now(),
            ]);

            Log::info('[WineContainerStockService] Embotellado registrado', [
                'bottling_id'  => $bottling->id,
                'container_id' => $bottling->container_id,
                'quantity_l'   => $qty,
            ]);
        });
    }

    /**
     * Revierte un embotellado: restaura wine_volume_liters al contenedor.
     */
    public function revertBottling(WineBottling $bottling): void
    {
        if (! $bottling->container_id) {
            return;
        }

        DB::transaction(function () use ($bottling) {
            $qty = (float) $bottling->quantity_liters;

            $container = Container::lockForUpdate()->find($bottling->container_id);
            if (! $container) {
                return;
            }

            $container->wine_volume_liters += $qty;
            $container->save();

            $this->updateCurrentState($container, $bottling->wine_id, $qty);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $bottling->wine_id,
                'operation_type' => 'bottling_revert',
                'quantity'       => $qty,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);
        });
    }

    /**
     * Actualiza un embotellado: revierte el anterior y aplica el nuevo.
     *
     * @param array $oldData ['wine_id', 'container_id', 'quantity_liters']
     */
    public function updateBottling(WineBottling $bottling, array $oldData): void
    {
        DB::transaction(function () use ($bottling, $oldData) {
            $fake = new WineBottling($oldData);
            $this->revertBottling($fake);
            $this->recordBottling($bottling);
        });
    }

    // ─── Entrada inicial de vino ─────────────────────────────────────────────

    /**
     * Registra una entrada de stock: incrementa wine_volume_liters en el contenedor.
     * Sirve para cargar stock inicial o corregir inventario de forma auditada.
     */
    public function recordStockEntry(WineContainerStockEntry $entry): void
    {
        if (! $entry->container_id) {
            return;
        }

        DB::transaction(function () use ($entry) {
            $qty = (float) $entry->quantity_liters;

            $container = Container::lockForUpdate()->find($entry->container_id);
            if (! $container) {
                return;
            }

            $container->wine_volume_liters = $container->wine_volume_liters + $qty;
            $container->save();

            $this->updateCurrentState($container, $entry->wine_id, $qty);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $entry->wine_id,
                'operation_type' => 'wine_stock_entry',
                'quantity'       => $qty,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);

            Log::info('[WineContainerStockService] Entrada de stock registrada', [
                'entry_id'     => $entry->id,
                'container_id' => $entry->container_id,
                'wine_id'      => $entry->wine_id,
                'quantity_l'   => $qty,
                'source'       => $entry->source,
            ]);
        });
    }

    /**
     * Revierte una entrada de stock: descuenta los litros del contenedor.
     */
    public function revertStockEntry(WineContainerStockEntry $entry): void
    {
        if (! $entry->container_id) {
            return;
        }

        DB::transaction(function () use ($entry) {
            $qty = (float) $entry->quantity_liters;

            $container = Container::lockForUpdate()->find($entry->container_id);
            if (! $container) {
                return;
            }

            $container->wine_volume_liters = max(0, $container->wine_volume_liters - $qty);
            $container->save();

            $this->updateCurrentState($container, null, -$qty);

            ContainerHistory::create([
                'container_id'   => $container->id,
                'wine_id'        => $entry->wine_id,
                'operation_type' => 'wine_stock_entry_revert',
                'quantity'       => -$qty,
                'created_by'     => Auth::id(),
                'start_date'     => now(),
            ]);

            Log::info('[WineContainerStockService] Entrada de stock revertida', [
                'entry_id'     => $entry->id,
                'container_id' => $entry->container_id,
                'quantity_l'   => $qty,
            ]);
        });
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * Actualiza o crea el ContainerCurrentState para el contenedor.
     * Solo actualiza wine_id y registra el movimiento de vino.
     */
    private function updateCurrentState(Container $container, ?int $wineId, float $delta): void
    {
        $state = ContainerCurrentState::lockForUpdate()
            ->firstOrNew(['container_id' => $container->id]);

        // Si hay delta positivo (entrada de vino), actualizar wine_id
        if ($delta > 0 && $wineId) {
            $state->wine_id = $wineId;
        }

        // Si queda sin vino, limpiar wine_id (solo si no tiene cosecha tampoco)
        if ($delta < 0 && $container->wine_volume_liters <= 0 && ! $state->harvest_id) {
            $state->wine_id = null;
        }

        $state->last_movement_at = now();
        $state->last_movement_by = Auth::id();
        $state->save();
    }

    /**
     * Registra una entrada en container_histories para audit trail.
     * $wineIdOverride permite especificar un wine_id distinto al transfer->wine_id
     * (necesario para blending, donde el origen tiene un vino diferente al resultado).
     */
    private function recordHistory(Container $container, WineTransfer $transfer, string $operationType, float $quantity, ?int $wineIdOverride = null): void
    {
        ContainerHistory::create([
            'container_id'   => $container->id,
            'wine_id'        => $wineIdOverride ?? $transfer->wine_id,
            'operation_type' => $operationType,
            'quantity'       => $quantity,
            'created_by'     => Auth::id(),
            'start_date'     => now(),
        ]);
    }
}
