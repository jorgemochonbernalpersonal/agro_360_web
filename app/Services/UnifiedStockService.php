<?php

namespace App\Services;

use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductLot;

class UnifiedStockService
{
    public function __construct(private ContainerStockService $containerStock) {}

    /**
     * Reserve or sell a newly created invoice item (harvest or wine_lot).
     *
     * Called from Producer/Invoices/Create and Edit when items are created while
     * bypassing InvoiceItemObserver. Validates harvest ownership against $userId.
     * For wine_lot items, moves available → reserved (or reserved → sold if sent).
     */
    public function reserveOrSell(Invoice $invoice, InvoiceItem $item, int $userId, float $qty): void
    {
        if ($item->harvest_id) {
            $harvest = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', $userId))
                ->find($item->harvest_id);
            if (! $harvest) {
                throw new \RuntimeException("La cosecha #{$item->harvest_id} no te pertenece.");
            }
            if ($invoice->status === 'draft') {
                $this->containerStock->reserveStock($harvest, $item);
            } else {
                $this->containerStock->directSale($harvest, $item, $invoice->invoice_number ?? '');
            }
        } elseif ($item->wine_lot_id) {
            $lot = ProductLot::where('user_id', $userId)->lockForUpdate()->find($item->wine_lot_id);
            if ($lot) {
                ProductStockService::moveOnCreate($invoice, $item, $lot, $qty);
            }
        }
    }

    /**
     * Cancel all stock for an invoice before re-creating its items (edit flow).
     *
     * The invoice must have 'items.harvest' already loaded by the caller.
     * Handles wine_lot items in batch, then harvest items one by one.
     */
    public function cancelAllForEdit(Invoice $invoice): void
    {
        ProductStockService::moveForInvoice($invoice, 'cancel');

        foreach ($invoice->items as $item) {
            if ($item->harvest_id && $item->harvest) {
                if ($invoice->status === 'draft') {
                    $this->containerStock->unreserveStock($item->harvest, $item);
                } else {
                    $this->containerStock->releaseFromInvoice($item->harvest, $item, $invoice->status);
                }
            }
        }
    }

    /**
     * Confirm delivery for all items (delivery_status → delivered).
     *
     * Moves wine_lot items reserved→sold. For harvest items, only acts when
     * invoice is draft (InvoiceObserver handles the sent case via status machine).
     * The invoice must have 'items.harvest' already loaded by the caller.
     */
    public function confirmDelivery(Invoice $invoice): void
    {
        ProductStockService::moveForInvoice($invoice, 'deliver');

        if ($invoice->status === 'draft') {
            foreach ($invoice->items as $item) {
                if ($item->harvest_id && $item->harvest) {
                    $this->containerStock->confirmSale($item->harvest, $item, $invoice->invoice_number ?? '');
                }
            }
        }
    }

    /**
     * Cancel delivery for all items (delivery_status → cancelled).
     *
     * Restores wine_lot items and harvest items to their pre-delivery stock state.
     * The invoice must have 'items.harvest' already loaded by the caller.
     */
    public function cancelDelivery(Invoice $invoice): void
    {
        ProductStockService::moveForInvoice($invoice, 'cancel');

        foreach ($invoice->items as $item) {
            if ($item->harvest_id && $item->harvest) {
                $this->containerStock->releaseFromInvoice($item->harvest, $item, $invoice->status);
            }
        }
    }
}
