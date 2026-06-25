<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\ProductLot;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductSaleService
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Crea una factura borrador de venta de producto con sus líneas y mueve stock.
     * Devuelve la Invoice creada (delivery_note_code ya poblado).
     */
    public function createSaleInvoice(
        int $userId,
        \App\Models\Client $client,
        array $invoiceFields,
        array $items,
        Collection $taxRates,
        bool $isGift
    ): Invoice {
        return DB::transaction(function () use ($userId, $client, $invoiceFields, $items, $taxRates, $isGift) {
            $noteCode = $this->invoiceService->generateDeliveryNoteCode($userId, false, '');
            $totals = $this->invoiceService->calculateVatTotals($items, $taxRates);
            $multiplyGift = $isGift ? 0 : 1;

            $invoice = Invoice::create(array_merge($invoiceFields, [
                'user_id' => $userId,
                'client_id' => $client->id,
                'invoice_type' => 'wine_sale',
                'delivery_note_code' => $noteCode,
                'delivery_status' => 'pending',
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'gift' => $isGift,
                'billing_first_name' => $client->first_name,
                'billing_last_name' => $client->last_name,
                'billing_company_name' => $client->company_name,
                'billing_email' => $client->email,
                'billing_phone' => $client->phone,
                'subtotal' => round($totals['gross_subtotal'] * $multiplyGift, 3),
                'discount_amount' => round($totals['discount_amount'] * $multiplyGift, 3),
                'tax_base' => round($totals['tax_base'] * $multiplyGift, 3),
                'tax_amount' => round($totals['tax_amount'] * $multiplyGift, 3),
                'total_amount' => round($totals['total'] * $multiplyGift, 3),
            ]));

            $this->createItems($invoice, $items, $taxRates, $multiplyGift);

            return $invoice;
        });
    }

    /**
     * Actualiza una factura de venta: cancela stock antiguo, borra líneas,
     * actualiza cabecera y recrea líneas con nuevo stock.
     */
    public function updateSaleInvoice(
        Invoice $invoice,
        \App\Models\Client $client,
        array $invoiceFields,
        array $items,
        Collection $taxRates,
        bool $isGift
    ): void {
        DB::transaction(function () use ($invoice, $client, $invoiceFields, $items, $taxRates, $isGift) {
            $invoice->load('items.wineLot');
            ProductStockService::moveForInvoice($invoice, 'cancel');

            InvoiceItem::withoutEvents(fn () => $invoice->items()->delete());

            $totals = $this->invoiceService->calculateVatTotals($items, $taxRates);
            $multiplyGift = $isGift ? 0 : 1;

            $invoice->update(array_merge($invoiceFields, [
                'client_id' => $client->id,
                'billing_first_name' => $client->first_name,
                'billing_last_name' => $client->last_name,
                'billing_company_name' => $client->company_name,
                'billing_email' => $client->email,
                'billing_phone' => $client->phone,
                'gift' => $isGift,
                'subtotal' => round($totals['gross_subtotal'] * $multiplyGift, 3),
                'discount_amount' => round($totals['discount_amount'] * $multiplyGift, 3),
                'tax_base' => round($totals['tax_base'] * $multiplyGift, 3),
                'tax_amount' => round($totals['tax_amount'] * $multiplyGift, 3),
                'total_amount' => round($totals['total'] * $multiplyGift, 3),
            ]));

            InvoiceItem::withoutEvents(function () use ($invoice, $items, $taxRates, $multiplyGift) {
                $this->createItems($invoice, $items, $taxRates, $multiplyGift);
            });
        });
    }

    private function createItems(Invoice $invoice, array $items, Collection $taxRates, int $multiplyGift): void
    {
        foreach ($items as $item) {
            $tax = $item['tax_id'] ? ($taxRates[$item['tax_id']] ?? null) : null;
            $line = $this->invoiceService->calculateVatLine($item, $tax);
            $qty = $line['quantity'];

            $lot = $item['wine_lot_id']
                ? ProductLot::where('user_id', $invoice->user_id)->lockForUpdate()->find($item['wine_lot_id'])
                : null;

            $createdItem = $invoice->items()->create([
                'wine_lot_id' => $lot?->id,
                'concept_type' => $item['concept_type'] ?? ($lot ? 'wine' : 'other'),
                'name' => $item['name'],
                'description' => $item['description'] ?: null,
                'sku' => $item['sku'] ?: ($lot?->sku ?? null),
                'quantity' => $qty,
                'unit_price' => $line['unit_price'],
                'discount_percentage' => $line['discount_percentage'],
                'discount_amount' => $line['discount_amount'] * $multiplyGift,
                'tax_id' => $tax?->id,
                'tax_name' => $tax?->name,
                'tax_rate' => $line['tax_rate'],
                'subtotal' => $line['subtotal'] * $multiplyGift,
                'tax_base' => $line['tax_base'] * $multiplyGift,
                'tax_amount' => $line['tax_amount'] * $multiplyGift,
                'total' => $line['total'] * $multiplyGift,
            ]);

            if ($lot) {
                ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
            }
        }
    }
}
