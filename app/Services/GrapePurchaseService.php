<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class GrapePurchaseService
{
    public function __construct(private InvoiceService $invoiceService) {}

    /**
     * Crea una liquidación de vendimia con sus líneas.
     * La notificación al viticultor queda fuera (debe llamarse tras el retorno).
     */
    public function createInvoice(int $wineryId, User $viticulturist, array $invoiceFields, array $lines): Invoice
    {
        return DB::transaction(function () use ($wineryId, $viticulturist, $invoiceFields, $lines) {
            ['number' => $number, 'noteCode' => $noteCode] =
                $this->invoiceService->generateSequentialNumber($wineryId, 'grape_purchase_seq', 'GP-', 'LIQ-');

            $totals = $this->invoiceService->calculateIrpfTotals($lines);

            $invoice = Invoice::create(array_merge($invoiceFields, [
                'user_id' => $wineryId,
                'invoice_type' => 'grape_purchase',
                'viticulturist_id' => $viticulturist->id,
                'invoice_number' => $number,
                'delivery_note_code' => $noteCode,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'billing_first_name' => $viticulturist->name,
                'billing_email' => $viticulturist->email,
                // NIF del viticultor: obligatorio como destinatario en Verifactu.
                'billing_company_document' => $viticulturist->dni,
                'subtotal' => $totals['subtotal'],
                'tax_base' => $totals['tax_base'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total'],
            ]));

            $this->createLines($invoice, $lines, $wineryId, $viticulturist->id, null);

            return $invoice;
        });
    }

    /**
     * Actualiza cabecera y recrea líneas de una liquidación en borrador.
     */
    public function updateInvoice(Invoice $invoice, array $invoiceFields, array $lines, int $wineryId): void
    {
        DB::transaction(function () use ($invoice, $invoiceFields, $lines, $wineryId) {
            $totals = $this->invoiceService->calculateIrpfTotals($lines);

            $invoice->update(array_merge($invoiceFields, [
                'subtotal' => $totals['subtotal'],
                'tax_base' => $totals['tax_base'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total'],
            ]));

            $invoice->items()->delete();

            $this->createLines($invoice, $lines, $wineryId, (int) $invoice->viticulturist_id, $invoice->id);
        });
    }

    private function createLines(Invoice $invoice, array $lines, int $wineryId, int $viticulturistId, ?int $excludeInvoiceId): void
    {
        foreach ($lines as $line) {
            $harvest = $this->invoiceService->validateWineryHarvestOwnership(
                (int) $line['harvest_id'],
                $wineryId,
                $viticulturistId,
                $excludeInvoiceId,
            );

            $amounts = $this->invoiceService->calculateIrpfLine($line);

            $variety = $harvest->plotPlanting?->grapeVariety->name ?? 'uva';
            $description = $line['description'] ?: "Vendimia #{$harvest->id} - {$variety}";

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'harvest_id' => $harvest->id,
                'concept_type' => 'harvest',
                'name' => $description,
                'description' => $line['description'] ?: null,
                'quantity' => $amounts['quantity'],
                'unit_price' => $amounts['unit_price'],
                'tax_rate' => $amounts['tax_rate'],
                'subtotal' => $amounts['subtotal'],
                'tax_base' => $amounts['tax_base'],
                'tax_amount' => $amounts['tax_amount'],
                'total' => $amounts['total'],
            ]);
        }
    }
}
