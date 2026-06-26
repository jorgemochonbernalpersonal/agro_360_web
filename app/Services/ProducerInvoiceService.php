<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProducerInvoiceService
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Crea una factura borrador de productor con líneas y reserva/vende stock.
     * Devuelve la Invoice creada (delivery_note_code ya poblado).
     */
    public function createInvoice(
        int $userId,
        array $invoiceFields,
        array $items,
        Collection $taxRates
    ): Invoice {
        return DB::transaction(function () use ($userId, $invoiceFields, $items, $taxRates) {
            $noteCode = $this->invoiceService->generateDeliveryNoteCode(
                $userId,
                $invoiceFields['delivery_note_code_modified'] ?? false,
                $invoiceFields['delivery_note_code_custom'] ?? '',
            );

            $totals = $this->invoiceService->calculateVatTotals($items, $taxRates);

            $invoice = Invoice::create(array_merge(
                array_diff_key($invoiceFields, array_flip(['delivery_note_code_modified', 'delivery_note_code_custom'])),
                [
                    'user_id' => $userId,
                    'invoice_type' => 'producer_sale',
                    'delivery_note_code' => $noteCode,
                    'status' => 'draft',
                    'delivery_status' => 'pending',
                    'payment_status' => 'unpaid',
                    'invoice_number' => null,
                    'subtotal' => $totals['gross_subtotal'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_base' => $totals['tax_base'],
                    'tax_rate' => $totals['effective_tax_rate'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                ]
            ));

            $stockService = app(UnifiedStockService::class);

            InvoiceItem::withoutEvents(function () use ($invoice, $items, $taxRates, $stockService, $userId) {
                $this->createItems($invoice, $items, $taxRates, $stockService, $userId);
            });

            return $invoice;
        });
    }

    /**
     * Actualiza una factura de productor: cancela stock, borra líneas,
     * recalcula totales y recrea líneas con nuevo stock.
     */
    public function updateInvoice(
        Invoice $invoice,
        array $invoiceFields,
        array $items,
        Collection $taxRates,
        int $userId
    ): void {
        DB::transaction(function () use ($invoice, $invoiceFields, $items, $taxRates, $userId) {
            $invoice->load('items.harvest', 'items.wineLot');

            $stockService = app(UnifiedStockService::class);
            $stockService->cancelAllForEdit($invoice);

            InvoiceItem::withoutEvents(fn () => $invoice->items()->delete());

            $totals = $this->invoiceService->calculateVatTotals($items, $taxRates);

            $invoice->update(array_merge($invoiceFields, [
                'subtotal' => $totals['gross_subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_base' => $totals['tax_base'],
                'tax_rate' => $totals['effective_tax_rate'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total'],
            ]));

            InvoiceItem::withoutEvents(function () use ($invoice, $items, $taxRates, $stockService, $userId) {
                $this->createItems($invoice, $items, $taxRates, $stockService, $userId);
            });

            $invoice->logAction('updated', 'Factura de productor actualizada', [
                'client_id' => ['old' => $invoice->getOriginal('client_id'), 'new' => $invoiceFields['client_id'] ?? null],
                'total_amount' => ['old' => $invoice->getOriginal('total_amount'), 'new' => $totals['total']],
                'items_count' => count($items),
            ]);
        });
    }

    private function createItems(Invoice $invoice, array $items, Collection $taxRates, UnifiedStockService $stockService, int $userId): void
    {
        foreach ($items as $item) {
            $tax = ($item['tax_id'] ?? null) ? ($taxRates[$item['tax_id']] ?? null) : null;
            $line = $this->invoiceService->calculateVatLine($item, $tax);
            $qty = $line['quantity'];

            $createdItem = $invoice->items()->create([
                'harvest_id' => $item['harvest_id'] ?? null,
                'wine_lot_id' => $item['wine_lot_id'] ?? null,
                'concept_type' => $item['concept_type'] ?? 'other',
                'name' => $item['name'],
                'description' => $item['description'] ?: null,
                'sku' => $item['sku'] ?: null,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'unidades',
                'unit_price' => $line['unit_price'],
                'discount_percentage' => $line['discount_percentage'],
                'discount_amount' => $line['discount_amount'],
                'tax_id' => $tax?->id,
                'tax_name' => $tax?->name,
                'tax_rate' => $line['tax_rate'],
                'tax_base' => $line['tax_base'],
                'tax_amount' => $line['tax_amount'],
                'subtotal' => $line['subtotal'],
                'total' => $line['total'],
            ]);

            $stockService->reserveOrSell($invoice, $createdItem, $userId, $qty);
        }
    }
}
