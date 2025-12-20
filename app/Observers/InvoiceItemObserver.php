<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use App\Models\HarvestStock;
use App\Models\ContainerState;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceItemObserver
{
    /**
     * Handle the InvoiceItem "created" event.
     * Cuando se crea un item de factura con harvest, reservar stock
     */
    public function created(InvoiceItem $item): void
    {
        // Solo procesar si está vinculado a harvest
        if (!$item->harvest_id || !$item->invoice) {
            return;
        }

        try {
            // Si la factura es draft, reservar stock
            if ($item->invoice->status === 'draft') {
                $this->reserveStock($item);
            }
            // Si la factura ya está aprobada, marcar como vendido directamente
            elseif (in_array($item->invoice->status, ['sent', 'paid'])) {
                $this->markAsSold($item);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar item de factura', [
                'invoice_item_id' => $item->id,
                'harvest_id' => $item->harvest_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the InvoiceItem "updated" event.
     * Cuando se actualiza la cantidad, ajustar reserva/venta
     */
    public function updated(InvoiceItem $item): void
    {
        if (!$item->harvest_id || !$item->invoice) {
            return;
        }

        $oldQuantity = $item->getOriginal('quantity');
        $newQuantity = $item->quantity;

        if ($oldQuantity == $newQuantity) {
            return; // No cambió la cantidad
        }

        try {
            $difference = $newQuantity - $oldQuantity;
            $harvest = $item->harvest;
            $lastStock = $harvest->stockMovements()->latest()->first();

            if (!$lastStock) {
                return;
            }

            $status = $item->invoice->status;

            if ($status === 'draft') {
                // Ajustar reserva
                $this->adjustReservation($item, $lastStock, $difference);
            } elseif (in_array($status, ['approved', 'sent', 'paid'])) {
                // Ajustar venta
                $this->adjustSale($item, $lastStock, $difference);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar item de factura', [
                'invoice_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the InvoiceItem "deleting" event.
     * Liberar stock cuando se elimina un item
     */
    public function deleting(InvoiceItem $item): void
    {
        if (!$item->harvest_id || !$item->invoice) {
            return;
        }

        try {
            $harvest = $item->harvest;
            $lastStock = $harvest->stockMovements()->latest()->first();

            if (!$lastStock) {
                return;
            }

            $status = $item->invoice->status;

            if ($status === 'draft') {
                // Liberar reserva
                $this->unreserveStock($item, $lastStock);
            } elseif (in_array($status, ['approved', 'sent', 'paid'])) {
                // Devolver venta
                $this->returnStock($item, $lastStock);
            }
        } catch (\Exception $e) {
            Log::error('Error al eliminar item de factura', [
                'invoice_item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reservar stock para venta
     */
    protected function reserveStock(InvoiceItem $item): void
    {
        $harvest = $item->harvest;
        $lastStock = $harvest->stockMovements()->latest()->first();

        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0, // No cambia el total, solo el estado
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty - $item->quantity,
            'reserved_qty' => $lastStock->reserved_qty + $item->quantity,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf('Reservado para factura #%s', $item->invoice->invoice_number ?? 'DRAFT'),
            'reference_number' => $item->invoice->invoice_number,
        ]);

        // Actualizar contenedor
        $this->updateContainerState($harvest, 0, -$item->quantity, $item->quantity);
    }

    /**
     * Marcar como vendido
     */
    protected function markAsSold(InvoiceItem $item): void
    {
        $harvest = $item->harvest;
        $lastStock = $harvest->stockMovements()->latest()->first();

        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'sale',
            'quantity_change' => 0, // Total no cambia, solo estado
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty - $item->quantity,
            'reserved_qty' => $lastStock->reserved_qty,
            'sold_qty' => $lastStock->sold_qty + $item->quantity,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf('Venta confirmada - Factura #%s', $item->invoice->invoice_number),
            'reference_number' => $item->invoice->invoice_number,
        ]);

        // Actualizar contenedor
        $this->updateContainerState($harvest, 0, -$item->quantity, 0, $item->quantity);
    }

    /**
     * Ajustar reserva
     */
    protected function adjustReservation(InvoiceItem $item, $lastStock, float $difference): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'reserve',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty - $difference,
            'reserved_qty' => $lastStock->reserved_qty + $difference,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf('Ajuste de reserva: %+.3f kg', $difference),
        ]);

        $this->updateContainerState($item->harvest, 0, -$difference, $difference);
    }

    /**
     * Ajustar venta
     */
    protected function adjustSale(InvoiceItem $item, $lastStock, float $difference): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'sale',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty - $difference,
            'reserved_qty' => $lastStock->reserved_qty,
            'sold_qty' => $lastStock->sold_qty + $difference,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => sprintf('Ajuste de venta: %+.3f kg', $difference),
        ]);

        $this->updateContainerState($item->harvest, 0, -$difference, 0, $difference);
    }

    /**
     * Liberar reserva
     */
    protected function unreserveStock(InvoiceItem $item, $lastStock): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'unreserve',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty + $item->quantity,
            'reserved_qty' => $lastStock->reserved_qty - $item->quantity,
            'sold_qty' => $lastStock->sold_qty,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => 'Reserva liberada - Item eliminado',
        ]);

        $this->updateContainerState($item->harvest, 0, $item->quantity, -$item->quantity);
    }

    /**
     * Devolver venta
     */
    protected function returnStock(InvoiceItem $item, $lastStock): void
    {
        HarvestStock::create([
            'harvest_id' => $item->harvest_id,
            'container_id' => $item->harvest->container_id,
            'user_id' => Auth::id(),
            'invoice_item_id' => $item->id,
            'movement_type' => 'return',
            'quantity_change' => 0,
            'quantity_after' => $lastStock->quantity_after,
            'available_qty' => $lastStock->available_qty + $item->quantity,
            'reserved_qty' => $lastStock->reserved_qty,
            'sold_qty' => $lastStock->sold_qty - $item->quantity,
            'gifted_qty' => $lastStock->gifted_qty,
            'lost_qty' => $lastStock->lost_qty,
            'notes' => 'Devolución - Venta cancelada',
        ]);

        $this->updateContainerState($item->harvest, 0, $item->quantity, 0, -$item->quantity);
    }

    /**
     * Actualizar estado del contenedor
     */
    protected function updateContainerState(
        $harvest,
        float $totalChange = 0,
        float $availableChange = 0,
        float $reservedChange = 0,
        float $soldChange = 0
    ): void {
        if (!$harvest->container_id) {
            return;
        }

        $state = ContainerState::where('container_id', $harvest->container_id)->first();
        
        if ($state) {
            $state->update([
                'total_quantity' => $state->total_quantity + $totalChange,
                'available_qty' => $state->available_qty + $availableChange,
                'reserved_qty' => $state->reserved_qty + $reservedChange,
                'sold_qty' => $state->sold_qty + $soldChange,
                'last_movement_at' => now(),
                'last_movement_by' => Auth::id(),
            ]);
        }
    }
}
