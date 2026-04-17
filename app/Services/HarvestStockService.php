<?php

namespace App\Services;

use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Manages the three-bucket stock system for viticulturist harvest sales.
 *
 * Buckets (mirrored in HarvestStock append-only ledger):
 *   available_qty — ready to sell
 *   reserved_qty  — committed to an active invoice, awaiting delivery
 *   sold_qty      — definitively delivered
 *
 * All public methods must be called INSIDE an open DB transaction.
 * HarvestStock rows are locked with lockForUpdate() per harvest.
 */
class HarvestStockService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Invoice created: reserve all harvest items (available → reserved).
     * Call AFTER all InvoiceItems have been persisted.
     *
     * @throws RuntimeException if available stock is insufficient
     */
    public static function reserveForInvoice(Invoice $invoice): void
    {
        $items = $invoice->items()
            ->where('concept_type', 'harvest')
            ->whereNotNull('harvest_id')
            ->get();

        foreach ($items as $item) {
            self::reserve($item->harvest_id, (float) $item->quantity, $invoice, $item);
        }
    }

    /**
     * Invoice delivered: reserved → sold for all harvest items.
     * Call BEFORE updating delivery_status on the invoice.
     */
    public static function deliverForInvoice(Invoice $invoice): void
    {
        $items = $invoice->items()
            ->where('concept_type', 'harvest')
            ->whereNotNull('harvest_id')
            ->get();

        foreach ($items as $item) {
            self::deliver($item->harvest_id, (float) $item->quantity, $invoice, $item);
        }
    }

    /**
     * Invoice cancelled: restore stock for all harvest items.
     * Determines source bucket from invoice's current delivery_status.
     * Call BEFORE updating delivery_status/status on the invoice.
     */
    public static function cancelForInvoice(Invoice $invoice): void
    {
        $items = $invoice->items()
            ->where('concept_type', 'harvest')
            ->whereNotNull('harvest_id')
            ->get();

        $fromSold = $invoice->delivery_status === 'delivered';

        foreach ($items as $item) {
            self::restore($item->harvest_id, (float) $item->quantity, $invoice, $item, $fromSold);
        }
    }

    /**
     * Before editing invoice items: unreserve the current set of harvest items.
     * Pass the already-loaded collection from DB (before items are deleted).
     */
    public static function unreserveItems(Collection $existingItems, Invoice $invoice): void
    {
        foreach ($existingItems as $item) {
            if ($item->concept_type !== 'harvest' || !$item->harvest_id) continue;
            self::unreserve($item->harvest_id, (float) $item->quantity, $invoice, $item);
        }
    }

    /**
     * After editing invoice items: reserve or deliver one item.
     * Used when recreating items in Edit — call per item right after persisting it.
     *
     * @param string $deliveryStatus  The invoice's new delivery_status value
     * @throws RuntimeException if available stock is insufficient
     */
    public static function moveOnItemSave(Invoice $invoice, InvoiceItem $item, string $deliveryStatus): void
    {
        if ($item->concept_type !== 'harvest' || !$item->harvest_id) return;

        $qty = (float) $item->quantity;

        match ($deliveryStatus) {
            'pending', 'in_transit' => self::reserve($item->harvest_id, $qty, $invoice, $item),
            'delivered'             => self::reserveThenDeliver($item->harvest_id, $qty, $invoice, $item),
            default                 => null, // cancelled — leave as available
        };
    }

    // ── Private stock movements ────────────────────────────────────────────────

    private static function reserve(int $harvestId, float $qty, Invoice $invoice, InvoiceItem $item): void
    {
        $state = self::currentState($harvestId);

        if ($qty > $state['available_qty']) {
            throw new RuntimeException(
                "Stock insuficiente para cosecha #{$harvestId}: disponible {$state['available_qty']} kg, solicitado {$qty} kg."
            );
        }

        $ref = $invoice->delivery_note_code ?: $invoice->invoice_number;

        self::appendLedger($harvestId, $item, $invoice, 'reserve', 0, [
            'available_qty' => $state['available_qty'] - $qty,
            'reserved_qty'  => $state['reserved_qty'] + $qty,
            'sold_qty'      => $state['sold_qty'],
        ], "Reservado para {$ref}");
    }

    private static function unreserve(int $harvestId, float $qty, Invoice $invoice, InvoiceItem $item): void
    {
        $state = self::currentState($harvestId);

        // Guard: nothing reserved (invoice created before stock system was active).
        // Unreserving 0 would inflate available_qty — skip silently.
        if ($state['reserved_qty'] <= 0) {
            return;
        }

        // Only release what's actually reserved (defensive clamp)
        $actualQty = min($qty, $state['reserved_qty']);

        self::appendLedger($harvestId, $item, $invoice, 'unreserve', 0, [
            'available_qty' => $state['available_qty'] + $actualQty,
            'reserved_qty'  => $state['reserved_qty'] - $actualQty,
            'sold_qty'      => $state['sold_qty'],
        ], "Reserva liberada de {$invoice->invoice_number}");
    }

    private static function deliver(int $harvestId, float $qty, Invoice $invoice, InvoiceItem $item): void
    {
        $state = self::currentState($harvestId);

        // Guard: if reserved_qty < qty the stock was already moved to sold by the observer
        // (e.g. InvoiceObserver::convertReservationsToSales on draft→sent). Skip to prevent
        // double-counting sold_qty.
        if ($state['reserved_qty'] < $qty) {
            return;
        }

        self::appendLedger($harvestId, $item, $invoice, 'sale', -$qty, [
            'available_qty' => $state['available_qty'],
            'reserved_qty'  => max(0, $state['reserved_qty'] - $qty),
            'sold_qty'      => $state['sold_qty'] + $qty,
        ], "Venta confirmada: {$invoice->invoice_number}");
    }

    /**
     * For the 'delivered' status on edit: go directly from available to sold
     * via two consecutive ledger entries (reserve + sale) for a clean audit trail.
     */
    private static function reserveThenDeliver(int $harvestId, float $qty, Invoice $invoice, InvoiceItem $item): void
    {
        self::reserve($harvestId, $qty, $invoice, $item);

        $state = self::currentState($harvestId); // re-read after reserve

        self::appendLedger($harvestId, $item, $invoice, 'sale', -$qty, [
            'available_qty' => $state['available_qty'],
            'reserved_qty'  => max(0, $state['reserved_qty'] - $qty),
            'sold_qty'      => $state['sold_qty'] + $qty,
        ], "Entrega directa: {$invoice->invoice_number}");
    }

    private static function restore(int $harvestId, float $qty, Invoice $invoice, InvoiceItem $item, bool $fromSold): void
    {
        $state        = self::currentState($harvestId);
        $movementType = $fromSold ? 'return' : 'unreserve';

        // Clamp: only take back what's actually in the source bucket
        $actualQty = $fromSold
            ? min($qty, max(0, $state['sold_qty']))
            : min($qty, max(0, $state['reserved_qty']));

        if ($actualQty <= 0) {
            return; // Nothing to restore — invoice predates stock system
        }

        self::appendLedger($harvestId, $item, $invoice, $movementType, $fromSold ? $actualQty : 0, [
            'available_qty' => $state['available_qty'] + $actualQty,
            'reserved_qty'  => $fromSold ? $state['reserved_qty'] : $state['reserved_qty'] - $actualQty,
            'sold_qty'      => $fromSold ? $state['sold_qty'] - $actualQty : $state['sold_qty'],
        ], "Cancelación de {$invoice->invoice_number}");
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Read current bucket state, locking the row for update.
     * Falls back to harvest.total_weight as initial available if no ledger exists.
     */
    private static function currentState(int $harvestId): array
    {
        $latest = HarvestStock::where('harvest_id', $harvestId)
            ->lockForUpdate()
            ->latest('id')
            ->first();

        if (!$latest) {
            $harvest = Harvest::findOrFail($harvestId);
            return [
                'available_qty' => (float) ($harvest->total_weight ?? 0),
                'reserved_qty'  => 0.0,
                'sold_qty'      => 0.0,
            ];
        }

        return [
            'available_qty' => (float) $latest->available_qty,
            'reserved_qty'  => (float) $latest->reserved_qty,
            'sold_qty'      => (float) $latest->sold_qty,
        ];
    }

    private static function appendLedger(
        int $harvestId,
        InvoiceItem $item,
        Invoice $invoice,
        string $movementType,
        float $quantityChange,
        array $buckets,
        string $notes
    ): void {
        HarvestStock::create([
            'harvest_id'       => $harvestId,
            'user_id'          => Auth::id(),
            'invoice_item_id'  => $item->id,
            'movement_type'    => $movementType,
            'quantity_change'  => $quantityChange,
            'quantity_after'   => $buckets['available_qty'] + $buckets['reserved_qty'] + $buckets['sold_qty'],
            'available_qty'    => $buckets['available_qty'],
            'reserved_qty'     => $buckets['reserved_qty'],
            'sold_qty'         => $buckets['sold_qty'],
            'reference_number' => $invoice->delivery_note_code ?? $invoice->invoice_number,
            'notes'            => $notes,
        ]);
    }
}
