<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\HarvestStock;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "updated" event.
     * Cuando una factura cambia de estado o delivery_status, actualizar el stock y fechas
     */
    public function updated(Invoice $invoice): void
    {
        $oldStatus = $invoice->getOriginal('status');
        $newStatus = $invoice->status;
        $oldDeliveryStatus = $invoice->getOriginal('delivery_status');
        $newDeliveryStatus = $invoice->delivery_status;
        $oldPaymentStatus = $invoice->getOriginal('payment_status');
        $newPaymentStatus = $invoice->payment_status;

        try {
            // Manejar cambios de delivery_status (prioridad principal para stock)
            if ($oldDeliveryStatus !== $newDeliveryStatus) {
                $this->handleDeliveryStatusChange($invoice, $oldDeliveryStatus, $newDeliveryStatus);
            }
            // Solo si NO hubo cambio de delivery_status, manejar cambios de status
            elseif ($oldStatus !== $newStatus) {
                $this->handleStatusChange($invoice, $oldStatus, $newStatus);
            }
            
            // Manejar cambios de payment_status (independiente de stock)
            if ($oldPaymentStatus !== $newPaymentStatus) {
                $this->handlePaymentStatusChange($invoice, $oldPaymentStatus, $newPaymentStatus);
            }
        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de factura', [
                'invoice_id' => $invoice->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'old_delivery_status' => $oldDeliveryStatus,
                'new_delivery_status' => $newDeliveryStatus,
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => $newPaymentStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Manejar cambios en delivery_status (controla stock principalmente)
     */
    protected function handleDeliveryStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        // pending → delivered: Convertir reservas a ventas
        if ($oldStatus === 'pending' && $newStatus === 'delivered') {
            $this->convertReservationsToSales($invoice);
            // Guardar fecha de entrega si no existe
            if (!$invoice->delivery_note_date) {
                $invoice->updateQuietly(['delivery_note_date' => now()]);
            }
        }
        
        // delivered → pending: Convertir ventas a reservas
        elseif ($oldStatus === 'delivered' && $newStatus === 'pending') {
            $this->convertSalesToReservations($invoice);
        }
        
        // delivered → in_transit: No cambiar stock (sigue como sold)
        elseif ($oldStatus === 'delivered' && $newStatus === 'in_transit') {
            // No hacer nada, el stock sigue vendido
        }
        
        // in_transit → delivered: No cambiar stock (sigue como sold)
        elseif ($oldStatus === 'in_transit' && $newStatus === 'delivered') {
            // No hacer nada, el stock sigue vendido
            // Guardar fecha de entrega si no existe
            if (!$invoice->delivery_note_date) {
                $invoice->updateQuietly(['delivery_note_date' => now()]);
            }
        }
        
        // Cualquier estado → cancelled: Liberar todo el stock
        elseif ($newStatus === 'cancelled') {
            $this->releaseAllStock($invoice);
        }
    }

    /**
     * Manejar cambios en status (solo si no hubo cambio de delivery_status)
     */
    protected function handleStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        // Draft → Sent: Convertir reservas en ventas y generar código de factura
        if ($oldStatus === 'draft' && $newStatus === 'sent') {
            // Generar código de factura si no existe
            if (!$invoice->invoice_number) {
                $settings = \App\Models\InvoicingSetting::getOrCreateForUser($invoice->user_id);
                $invoiceNumber = $settings->generateInvoiceCode();
                $settings->incrementInvoiceCounter();
                $invoice->updateQuietly(['invoice_number' => $invoiceNumber]);
            }
            $this->convertReservationsToSales($invoice);
        }
        
        // Sent → Draft: Convertir ventas en reservas
        elseif ($oldStatus === 'sent' && $newStatus === 'draft') {
            $this->convertSalesToReservations($invoice);
        }
        
        // Cualquier estado → Cancelled: Liberar todo el stock
        elseif ($newStatus === 'cancelled') {
            $this->releaseAllStock($invoice);
        }
    }

    /**
     * Manejar cambios en payment_status
     */
    protected function handlePaymentStatusChange(Invoice $invoice, string $oldStatus, string $newStatus): void
    {
        // Cualquier estado → paid: Guardar fecha de pago
        if ($newStatus === 'paid' && !$invoice->payment_date) {
            $invoice->updateQuietly(['payment_date' => now()]);
        }
        
        // paid → otro status: Limpiar fecha de pago
        elseif ($oldStatus === 'paid' && $newStatus !== 'paid') {
            $invoice->updateQuietly(['payment_date' => null]);
        }
    }

    /**
     * Handle the Invoice "deleting" event.
     * Liberar todo el stock antes de eliminar
     */
    public function deleting(Invoice $invoice): void
    {
        try {
            $this->releaseAllStock($invoice);
        } catch (\Exception $e) {
            Log::error('Error al eliminar factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Convertir reservas en ventas
     */
    protected function convertReservationsToSales(Invoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            if (!$item->harvest_id) {
                continue;
            }

            $harvest = $item->harvest;
            $lastStock = $harvest->stockMovements()->latest()->first();

            if (!$lastStock) {
                continue;
            }

            // Solo convertir si hay cantidad reservada
            if ($lastStock->reserved_qty >= $item->quantity) {
                HarvestStock::create([
                    'harvest_id' => $item->harvest_id,
                    'container_id' => $harvest->container_id,
                    'user_id' => Auth::id(),
                    'invoice_item_id' => $item->id,
                    'movement_type' => 'sale',
                    'quantity_change' => 0,
                    'quantity_after' => $lastStock->quantity_after,
                    'available_qty' => $lastStock->available_qty,
                    'reserved_qty' => $lastStock->reserved_qty - $item->quantity,
                    'sold_qty' => $lastStock->sold_qty + $item->quantity,
                    'gifted_qty' => $lastStock->gifted_qty,
                    'lost_qty' => $lastStock->lost_qty,
                    'notes' => sprintf(
                        'Venta confirmada - Factura #%s aprobada',
                        $invoice->invoice_number
                    ),
                    'reference_number' => $invoice->invoice_number,
                ]);

                // Actualizar contenedor
                if ($harvest->container_id) {
                    $state = \App\Models\ContainerState::where('container_id', $harvest->container_id)->first();
                    if ($state) {
                        $state->update([
                            'reserved_qty' => $state->reserved_qty - $item->quantity,
                            'sold_qty' => $state->sold_qty + $item->quantity,
                            'last_movement_at' => now(),
                            'last_movement_by' => Auth::id(),
                        ]);
                    }
                }
            }
        }

        Log::info('Reservas convertidas a ventas', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    /**
     * Convertir ventas en reservas (deshace una aprobación)
     */
    protected function convertSalesToReservations(Invoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            if (!$item->harvest_id) {
                continue;
            }

            $harvest = $item->harvest;
            $lastStock = $harvest->stockMovements()->latest()->first();

            if (!$lastStock) {
                continue;
            }

            HarvestStock::create([
                'harvest_id' => $item->harvest_id,
                'container_id' => $harvest->container_id,
                'user_id' => Auth::id(),
                'invoice_item_id' => $item->id,
                'movement_type' => 'reserve',
                'quantity_change' => 0,
                'quantity_after' => $lastStock->quantity_after,
                'available_qty' => $lastStock->available_qty,
                'reserved_qty' => $lastStock->reserved_qty + $item->quantity,
                'sold_qty' => $lastStock->sold_qty - $item->quantity,
                'gifted_qty' => $lastStock->gifted_qty,
                'lost_qty' => $lastStock->lost_qty,
                'notes' => sprintf(
                    'Venta revertida a reserva - Factura #%s vuelta a borrador',
                    $invoice->invoice_number
                ),
                'reference_number' => $invoice->invoice_number,
            ]);

            // Actualizar contenedor
            if ($harvest->container_id) {
                $state = \App\Models\ContainerState::where('container_id', $harvest->container_id)->first();
                if ($state) {
                    $state->update([
                        'reserved_qty' => $state->reserved_qty + $item->quantity,
                        'sold_qty' => $state->sold_qty - $item->quantity,
                        'last_movement_at' => now(),
                        'last_movement_by' => Auth::id(),
                    ]);
                }
            }
        }

        Log::info('Ventas revertidas a reservas', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
        ]);
    }

    /**
     * Liberar todo el stock (cancelación o eliminación)
     */
    protected function releaseAllStock(Invoice $invoice): void
    {
        $oldStatus = $invoice->getOriginal('status') ?? $invoice->status;

        foreach ($invoice->items as $item) {
            if (!$item->harvest_id) {
                continue;
            }

            $harvest = $item->harvest;
            $lastStock = $harvest->stockMovements()->latest()->first();

            if (!$lastStock) {
                continue;
            }

            $movementType = null;
            $newReserved = $lastStock->reserved_qty;
            $newSold = $lastStock->sold_qty;
            $newAvailable = $lastStock->available_qty;

            // Determinar de dónde liberar
            if ($oldStatus === 'draft') {
                $movementType = 'unreserve';
                $newReserved -= $item->quantity;
                $newAvailable += $item->quantity;
            } else {
                $movementType = 'return';
                $newSold -= $item->quantity;
                $newAvailable += $item->quantity;
            }

            HarvestStock::create([
                'harvest_id' => $item->harvest_id,
                'container_id' => $harvest->container_id,
                'user_id' => Auth::id(),
                'invoice_item_id' => $item->id,
                'movement_type' => $movementType,
                'quantity_change' => 0,
                'quantity_after' => $lastStock->quantity_after,
                'available_qty' => $newAvailable,
                'reserved_qty' => max(0, $newReserved),
                'sold_qty' => max(0, $newSold),
                'gifted_qty' => $lastStock->gifted_qty,
                'lost_qty' => $lastStock->lost_qty,
                'notes' => sprintf(
                    'Stock liberado - Factura #%s %s',
                    $invoice->invoice_number ?? 'SIN NÚMERO',
                    $invoice->status === 'cancelled' ? 'cancelada' : 'eliminada'
                ),
                'reference_number' => $invoice->invoice_number,
            ]);

            // Actualizar contenedor
            if ($harvest->container_id) {
                $state = \App\Models\ContainerState::where('container_id', $harvest->container_id)->first();
                if ($state) {
                    $availableChange = $item->quantity;
                    $reservedChange = $oldStatus === 'draft' ? -$item->quantity : 0;
                    $soldChange = $oldStatus !== 'draft' ? -$item->quantity : 0;

                    $state->update([
                        'available_qty' => $state->available_qty + $availableChange,
                        'reserved_qty' => max(0, $state->reserved_qty + $reservedChange),
                        'sold_qty' => max(0, $state->sold_qty + $soldChange),
                        'last_movement_at' => now(),
                        'last_movement_by' => Auth::id(),
                    ]);
                }
            }
        }

        Log::info('Todo el stock liberado', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'reason' => $invoice->status === 'cancelled' ? 'cancelled' : 'deleted',
        ]);
    }
}
