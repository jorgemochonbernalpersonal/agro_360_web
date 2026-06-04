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
     * Al añadir un item: reserva si la factura es draft, venta directa si es sent/approved.
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
            if (in_array($invoice->status, ['sent', 'approved'])) {
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
        $invoiceStatus = $item->invoice()->value('status');

        if (! in_array($invoiceStatus, ['draft', 'sent', 'approved'])) {
            return;
        }

        try {
            $this->stockService->adjustItemQuantity(
                $item->harvest,
                $item,
                $oldQty,
                $newQty,
                $invoiceStatus
            );
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al ajustar stock en updated', [
                'item_id' => $item->id,
                'old_qty' => $oldQty,
                'new_qty' => $newQty,
                'invoice_status' => $invoiceStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Al eliminar un item: devuelve stock según el estado de la factura.
     *
     * Si sent/approved o delivery_status=delivered → stock en sold → movement_type='return'
     * Si draft/pending → stock en reserved → movement_type='unreserve'
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

        $invoiceStatus = $invoice->status;
        $deliveryStatus = $invoice->delivery_status;

        try {
            if ($deliveryStatus === 'delivered' || in_array($invoiceStatus, ['sent', 'approved'])) {
                $this->stockService->releaseFromInvoice($item->harvest, $item, 'sent');
            } else {
                $this->stockService->unreserveStock($item->harvest, $item);
            }
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al liberar stock en deleting', [
                'item_id' => $item->id,
                'invoice_id' => $item->invoice_id,
                'invoice_status' => $invoiceStatus,
                'delivery_status' => $deliveryStatus,
                'harvest_id' => $item->harvest_id,
                'error' => $e->getMessage(),
            ]);
            // No re-throw: permitir que el item se elimine aunque falle el stock
        }
    }

    /**
     * Al restaurar un item soft-deleted en factura draft: vuelve a reservar.
     */
    public function restored(InvoiceItem $item): void
    {
        if (! $item->harvest_id || $item->invoice()->value('status') !== 'draft') {
            return;
        }

        try {
            $this->stockService->reserveStock($item->harvest, $item);
        } catch (\Exception $e) {
            Log::error('[InvoiceItemObserver] Error al re-reservar stock tras restaurar item', [
                'item_id' => $item->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
