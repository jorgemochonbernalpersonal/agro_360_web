<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     * Reservar stock cuando se añade un item a factura draft
     */
    public function created(InvoiceItem $item): void
    {
        // Solo procesar items de vendimia en facturas draft
        if (!$item->harvest_id || $item->invoice->status !== 'draft') {
            return;
        }

        try {
            $this->reserveStockForItem($item);
        } catch (\Exception $e) {
            Log::error('Error al reservar stock para item', [
                'item_id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'harvest_id' => $item->harvest_id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle the InvoiceItem "deleting" event.
     * Liberar stock cuando se elimina un item de factura draft
     */
    public function deleting(InvoiceItem $item): void
    {
        // Solo procesar items de vendimia en facturas draft
        if (!$item->harvest_id || $item->invoice->status !== 'draft') {
            return;
        }

        try {
            $this->releaseStockForItem($item);
        } catch (\Exception $e) {
            Log::error('Error al liberar stock de item', [
                'item_id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'harvest_id' => $item->harvest_id,
                'error' => $e->getMessage(),
            ]);
            // No re-lanzar, permitir eliminación aunque falle stock
        }
    }

    /**
     * Handle the InvoiceItem "restored" event.
     * Reservar stock cuando se restaura un item soft-deleted
     */
    public function restored(InvoiceItem $item): void
    {
        // Solo procesar items de vendimia en facturas draft
        if (!$item->harvest_id || $item->invoice->status !== 'draft') {
            return;
        }

        try {
            $this->reserveStockForItem($item);
        } catch (\Exception $e) {
            Log::error('Error al reservar stock al restaurar item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Reservar stock para un item
     */
    protected function reserveStockForItem(InvoiceItem $item): void
    {
        // 🔒 Lock para prevenir race conditions
        $harvest = Harvest::lockForUpdate()->find($item->harvest_id);
        $lastStock = $harvest->stockMovements()
            ->lockForUpdate()
            ->latest()
            ->first();

        // Si no hay stock inicial, crearlo automáticamente
        if (!$lastStock) {
            $lastStock = HarvestStock::create([
                'harvest_id' => $harvest->id,
                'container_id' => $harvest->container_id,
                'user_id' => Auth::id() ?? $item->invoice->user_id,
                'movement_type' => 'initial',
                'quantity_change' => $harvest->total_weight,
                'quantity_after' => $harvest->total_weight,
                'available_qty' => $harvest->total_weight,
                'reserved_qty' => 0,
                'sold_qty' => 0,
                'gifted_qty' => 0,
                'lost_qty' => 0,
                'notes' => 'Stock inicial de cosecha (auto-creado)',
            ]);

            Log::info('Stock inicial creado automáticamente', [
                'harvest_id' => $harvest->id,
                'total_weight' => $harvest->total_weight,
            ]);
        }

        // Verificar que hay stock disponible
        if ($lastStock->available_qty < $item->quantity) {
            throw new \Exception(sprintf(
                'Stock insuficiente para la cosecha #%d. Disponible: %.2f kg, Requerido: %.2f kg',
                $harvest->id,
                $lastStock->available_qty,
                $item->quantity
            ));
        }

        // Crear movimiento de reserva
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $harvest->container_id,
            'user_id' => Auth::id() ?? $item->invoice->user_id,
            'invoice_item_id' => $item->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty - $item->quantity,
            'reserved_qty' => $lastStock->reserved_qty + $item->quantity,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf(
                'Stock reservado - Item #%d en Factura #%s',
                $item->id,
                $item->invoice->delivery_note_code
            ),
            'reference_number' => $item->invoice->delivery_note_code,
        ]);

        // Actualizar ContainerCurrentState: restar cantidad reservada para reflejar disponibilidad
        if ($harvest->container_id) {
            $container = Container::find($harvest->container_id);
            if ($container) {
                $state = ContainerCurrentState::firstOrCreate(
                    ['container_id' => $container->id],
                    [
                        'harvest_id' => $harvest->id,
                        'current_quantity' => $harvest->total_weight,
                        'has_subproducts' => false,
                    ]
                );
                
                // Actualizar current_quantity restando la cantidad reservada
                // Esto refleja la cantidad disponible en el contenedor
                $newQuantity = max(0, $state->current_quantity - $item->quantity);
                $state->updateQuantity($newQuantity);
            }
        }

        // Validación de seguridad: Verificar que no hay stock negativo
        $newStock = HarvestStock::where('harvest_id', $item->harvest_id)
            ->latest()
            ->first();

        if ($newStock->available_qty < 0) {
            Log::critical('Stock negativo detectado', [
                'harvest_id' => $item->harvest_id,
                'available_qty' => $newStock->available_qty,
                'invoice_item_id' => $item->id,
            ]);
            throw new \Exception(
                'Error crítico: Se ha detectado stock negativo. Por favor, contacte con soporte técnico.'
            );
        }

        Log::info('Stock reservado para item', [
            'item_id' => $item->id,
            'harvest_id' => $item->harvest_id,
            'quantity' => $item->quantity,
        ]);
    }

    /**
     * Liberar stock de un item
     */
    protected function releaseStockForItem(InvoiceItem $item): void
    {
        // 🔒 Lock para prevenir race conditions
        $harvest = Harvest::lockForUpdate()->find($item->harvest_id);
        $lastStock = $harvest->stockMovements()
            ->lockForUpdate()
            ->latest()
            ->first();

        if (!$lastStock) {
            Log::warning('No hay stock para liberar', [
                'item_id' => $item->id,
                'harvest_id' => $item->harvest_id,
            ]);
            return;
        }

        // Crear movimiento de liberación
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $harvest->container_id,
            'user_id' => Auth::id() ?? $item->invoice->user_id,
            'invoice_item_id' => $item->id,
            'movement_type' => 'unreserve',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty + $item->quantity,
            'reserved_qty' => max(0, $lastStock->reserved_qty - $item->quantity),
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf(
                'Stock liberado - Item #%d eliminado de Factura #%s',
                $item->id,
                $item->invoice->delivery_note_code
            ),
            'reference_number' => $item->invoice->delivery_note_code,
        ]);

        // Actualizar ContainerCurrentState: incrementar cantidad al liberar reserva
        if ($harvest->container_id) {
            $container = Container::find($harvest->container_id);
            if ($container) {
                $state = ContainerCurrentState::where('container_id', $container->id)->first();
                if ($state) {
                    // Incrementar current_quantity al liberar la reserva
                    $newQuantity = $state->current_quantity + $item->quantity;
                    $state->updateQuantity($newQuantity);
                }
            }
        }

        Log::info('Stock liberado de item', [
            'item_id' => $item->id,
            'harvest_id' => $item->harvest_id,
            'quantity' => $item->quantity,
        ]);
    }
}
