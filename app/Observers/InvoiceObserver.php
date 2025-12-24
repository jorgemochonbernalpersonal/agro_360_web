<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Models\HarvestStock;
use App\Models\Container;
use App\Models\ContainerCurrentState;
use App\Models\ContainerHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     * Reservar stock cuando se crea una factura con items de vendimia
     */
    public function created(Invoice $invoice): void
    {
        try {
            // Eager load para evitar N+1 queries
            $invoice->load('items.harvest.stockMovements');
            
            // Solo reservar si es draft
            if ($invoice->status === 'draft') {
                $this->reserveStockForDraft($invoice);
            }
            
            // Establecer order_date si no existe
            if (!$invoice->order_date) {
                $invoice->updateQuietly(['order_date' => now()]);
            }
        } catch (\Exception $e) {
            Log::error('Error al crear factura - reserva de stock', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
            throw $e; // Re-lanzar para que la transacción falle
        }
    }

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

                // Actualizar contenedor: decrementar used_capacity porque se vendió
                if ($harvest->container_id) {
                    $container = Container::find($harvest->container_id);
                    if ($container) {
                        // Decrementar used_capacity porque se vendió
                        $container->decrementUsedCapacity($item->quantity);

                        // Actualizar estado actual
                        $state = ContainerCurrentState::where('container_id', $container->id)->first();
                        if ($state) {
                            $newQuantity = max(0, $state->current_quantity - $item->quantity);
                            $state->updateQuantity($newQuantity);
                        }

                        // Registrar en historial
                        ContainerHistory::create([
                            'container_id' => $container->id,
                            'harvest_id' => $harvest->id,
                            'operation_type' => 'sale',
                            'created_by' => Auth::id(),
                            'quantity' => -$item->quantity, // Negativo porque es salida
                            'start_date' => now(),
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

            // Actualizar contenedor: incrementar used_capacity porque se revierte la venta
            if ($harvest->container_id) {
                $container = Container::find($harvest->container_id);
                if ($container) {
                    // Incrementar used_capacity porque se revierte la venta
                    $container->incrementUsedCapacity($item->quantity);

                    // Actualizar estado actual
                    $state = ContainerCurrentState::where('container_id', $container->id)->first();
                    if ($state) {
                        $newQuantity = $state->current_quantity + $item->quantity;
                        $state->updateQuantity($newQuantity);
                    }

                    // Registrar en historial
                    ContainerHistory::create([
                        'container_id' => $container->id,
                        'harvest_id' => $harvest->id,
                        'operation_type' => 'adjustment',
                        'created_by' => Auth::id(),
                        'quantity' => $item->quantity, // Positivo porque se revierte
                        'start_date' => now(),
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

    /**
     * Reservar stock para factura draft recién creada
     */
    protected function reserveStockForDraft(Invoice $invoice): void
    {
        foreach ($invoice->items as $item) {
            if (!$item->harvest_id) {
                continue; // Solo procesar items de vendimia
            }

            $harvest = $item->harvest;
            
            // 🔒 LOCK para prevenir race conditions en stock
            $harvest = \App\Models\Harvest::lockForUpdate()->find($harvest->id);
            $lastStock = $harvest->stockMovements()
                ->lockForUpdate()
                ->latest()
                ->first();

            // Si no hay stock inicial, crearlo automáticamente
            if (!$lastStock) {
                $lastStock = HarvestStock::create([
                    'harvest_id' => $harvest->id,
                    'container_id' => $harvest->container_id,
                    'user_id' => Auth::id() ?? $invoice->user_id,
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
                'user_id' => Auth::id() ?? $invoice->user_id,
                'invoice_item_id' => $item->id,
                'movement_type' => 'reserve',
                'quantity_change' => 0, // No cambia cantidad total
                'quantity_after' => $lastStock->quantity_after,
                'available_qty' => $lastStock->available_qty - $item->quantity,
                'reserved_qty' => $lastStock->reserved_qty + $item->quantity,
                'sold_qty' => $lastStock->sold_qty,
                'gifted_qty' => $lastStock->gifted_qty,
                'lost_qty' => $lastStock->lost_qty,
                'notes' => sprintf(
                    'Stock reservado para factura draft - Albarán: %s',
                    $invoice->delivery_note_code
                ),
                'reference_number' => $invoice->delivery_note_code,
            ]);

            // Actualizar ContainerState
            if ($harvest->container_id) {
                $state = \App\Models\ContainerState::firstOrCreate(
                    ['container_id' => $harvest->container_id],
                    [
                        'content_type' => 'harvest',
                        'harvest_id' => $harvest->id,
                        'total_quantity' => $harvest->total_weight, // ✅ FIX: inicializar correctamente
                        'available_qty' => $harvest->total_weight,
                        'reserved_qty' => 0,
                        'sold_qty' => 0,
                    ]
                );

                $state->update([
                    'available_qty' => max(0, $state->available_qty - $item->quantity),
                    'reserved_qty' => $state->reserved_qty + $item->quantity,
                    'last_movement_at' => now(),
                    'last_movement_by' => Auth::id() ?? $invoice->user_id,
                ]);
            }
        }

        Log::info('Stock reservado para factura draft', [
            'invoice_id' => $invoice->id,
            'delivery_note_code' => $invoice->delivery_note_code,
            'items_count' => $invoice->items->count(),
        ]);
    }
}
