<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use App\Services\ContainerStockService;
use Illuminate\Support\Facades\Log;

class InvoiceItemObserver
{
    public function __construct(
        private ContainerStockService $stockService
    ) {}

    /**
     * Al añadir un item: reserva stock, salvo que la entrega ya esté confirmada
     * (delivery_status=delivered), único disparador de venta real.
     */
    public function created(InvoiceItem $item): void
    {
        if (! $item->harvest_id) {
            return;
        }

        $invoice = $item->invoice()->first();
        if (! $invoice || $invoice->status === 'cancelled') {
            return;
        }

        try {
            if ($invoice->delivery_status === 'delivered') {
                $this->stockService->directSale($item->harvest, $item, $invoice->invoice_number ?? '');
            } else {
                $this->stockService->reserveStock($item->harvest, $item);
            }
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al gestionar stock en created', [
                'item_id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'harvest_id' => $item->harvest_id,
                'invoice_status' => $invoice->status,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Al cambiar la cantidad de un item: ajusta la reserva o la venta.
     */
    public function updated(InvoiceItem $item): void
    {
        if (! $item->harvest_id) {
            return;
        }

        $oldQty = (float) $item->getOriginal('quantity');
        $newQty = (float) $item->quantity;

        if ($oldQty == $newQty) {
            return;
        }

        // Query fresh from DB to avoid stale cached relationship (invoice may have changed status)
        $invoice = $item->invoice()->first(['status', 'delivery_status']);

        if (! $invoice || in_array($invoice->status, ['cancelled'])) {
            return;
        }

        try {
            $this->stockService->adjustItemQuantity(
                $item->harvest,
                $item,
                $oldQty,
                $newQty,
                $invoice->delivery_status === 'delivered'
            );
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al ajustar stock en updated', [
                'item_id' => $item->id,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'invoice_status' => $invoice->status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Al eliminar un item: devuelve stock según si la entrega estaba confirmada.
     *
     * Si delivery_status=delivered → stock en sold → movement_type='return'
     * Si no → stock en reserved → movement_type='unreserve'
     */
    public function deleting(InvoiceItem $item): void
    {
        if (! $item->harvest_id) {
            return;
        }

        $invoice = $item->invoice()->first();
        if (! $invoice) {
            return;
        }

        $deliveryStatus = $invoice->delivery_status;

        try {
            if ($deliveryStatus === 'delivered') {
                $this->stockService->releaseFromInvoice($item->harvest, $item, 'sent');
            } else {
                $this->stockService->unreserveStock($item->harvest, $item);
            }
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al liberar stock en deleting', [
                'item_id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'invoice_status' => $invoice->status,
                'delivery_status' => $deliveryStatus,
                'harvest_id' => $item->harvest_id,
                'error' => $e->getMessage(),
            ]);
            // No re-throw: permitir que el item se elimine aunque falle el stock
        }
    }

    /**
     * Al restaurar un item soft-deleted: vuelve a reservar, salvo que la entrega
     * ya estuviera confirmada (delivery_status=delivered), en cuyo caso vuelve a vender.
     */
    public function restored(InvoiceItem $item): void
    {
        if (! $item->harvest_id) {
            return;
        }

        $invoice = $item->invoice()->first();
        if (! $invoice || $invoice->status === 'cancelled') {
            return;
        }

        try {
            if ($invoice->delivery_status === 'delivered') {
                $this->stockService->directSale($item->harvest, $item, $invoice->invoice_number ?? '');
            } else {
                $this->stockService->reserveStock($item->harvest, $item);
            }
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al re-reservar stock tras restaurar item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
