<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStockMovement;
use App\Models\WineLot;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Manages the three-bucket stock system for wine lots.
 *
 * Buckets:
 *   available  — ready to sell
 *   reserved   — committed to an active invoice, awaiting delivery
 *   sold       — definitively delivered
 *
 * All public methods must be called INSIDE an open DB transaction.
 * WineLot rows must already be locked with lockForUpdate() by the caller.
 */
class WineStockService
{
    /**
     * Process stock movement for every wine item on an invoice.
     *
     * Used for 'deliver' and 'cancel' actions where the invoice already exists.
     * Locks each wine_lot row within the active transaction.
     *
     * @param  string  $action  'deliver' | 'cancel'
     */
    public static function moveForInvoice(Invoice $invoice, string $action): void
    {
        foreach ($invoice->items as $item) {
            if ($item->concept_type !== 'wine' || !$item->wine_lot_id) {
                continue;
            }

            $lot = WineLot::lockForUpdate()->findOrFail($item->wine_lot_id);
            self::apply($invoice, $item, $lot, (float) $item->quantity, $action);
        }
    }

    /**
     * Move stock for a single item during invoice creation (available → reserved).
     *
     * The InvoiceItem must already be persisted before calling this.
     * The WineLot must already be locked with lockForUpdate() by the caller.
     *
     * @throws RuntimeException if available stock is insufficient
     */
    public static function moveOnCreate(Invoice $invoice, InvoiceItem $item, WineLot $lot, float $qty): void
    {
        self::apply($invoice, $item, $lot, $qty, 'create');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private static function apply(Invoice $invoice, InvoiceItem $item, WineLot $lot, float $qty, string $action): void
    {
        match ($action) {
            'create'  => self::onCreate($invoice, $item, $lot, $qty),
            'deliver' => self::onDeliver($invoice, $item, $lot, $qty),
            'cancel'  => self::onCancel($invoice, $item, $lot, $qty),
            default   => throw new RuntimeException("Unknown stock action: {$action}"),
        };
    }

    /**
     * Invoice created: available -= qty, reserved += qty
     */
    private static function onCreate(Invoice $invoice, InvoiceItem $item, WineLot $lot, float $qty): void
    {
        if ($qty > (float) $lot->available_quantity) {
            throw new RuntimeException(
                "Stock insuficiente para «{$lot->name}»: disponible {$lot->available_quantity} {$lot->unit}, solicitado {$qty}."
            );
        }

        $lot->decrement('available_quantity', $qty);
        $lot->increment('reserved_quantity', $qty);

        self::log($invoice, $item, $lot, $qty, 'create', 'available', 'reserved');
    }

    /**
     * Invoice delivered: reserved -= qty, sold += qty
     */
    private static function onDeliver(Invoice $invoice, InvoiceItem $item, WineLot $lot, float $qty): void
    {
        if ($qty > (float) $lot->reserved_quantity) {
            throw new RuntimeException(
                "Cantidad reservada insuficiente para «{$lot->name}»: reservado {$lot->reserved_quantity}, requerido {$qty}."
            );
        }

        $lot->decrement('reserved_quantity', $qty);
        $lot->increment('sold_quantity', $qty);

        self::log($invoice, $item, $lot, $qty, 'deliver', 'reserved', 'sold');
    }

    /**
     * Invoice cancelled: (reserved | sold) -= qty, available += qty
     *
     * Determines source bucket from the invoice's current delivery_status
     * (must be called BEFORE the status is updated on the invoice).
     * Clamps decrements at 0 to guard against pre-existing invoices that
     * were created before the stock system was in place.
     */
    private static function onCancel(Invoice $invoice, InvoiceItem $item, WineLot $lot, float $qty): void
    {
        if ($invoice->delivery_status === 'delivered') {
            $restoreFromSold = min($qty, max(0, (float) $lot->sold_quantity));
            if ($restoreFromSold > 0) {
                $lot->decrement('sold_quantity', $restoreFromSold);
            }
            self::log($invoice, $item, $lot, $qty, 'cancel', 'sold', 'available');
        } else {
            $restoreFromReserved = min($qty, max(0, (float) $lot->reserved_quantity));
            if ($restoreFromReserved > 0) {
                $lot->decrement('reserved_quantity', $restoreFromReserved);
            }
            self::log($invoice, $item, $lot, $qty, 'cancel', 'reserved', 'available');
        }

        $lot->increment('available_quantity', $qty);
    }

    private static function log(
        Invoice $invoice,
        InvoiceItem $item,
        WineLot $lot,
        float $qty,
        string $action,
        string $from,
        string $to
    ): void {
        InvoiceStockMovement::create([
            'invoice_id'      => $invoice->id,
            'invoice_item_id' => $item->id,
            'wine_lot_id'     => $lot->id,
            'user_id'         => Auth::id(),
            'qty'             => $qty,
            'action'          => $action,
            'from_bucket'     => $from,
            'to_bucket'       => $to,
        ]);
    }
}
