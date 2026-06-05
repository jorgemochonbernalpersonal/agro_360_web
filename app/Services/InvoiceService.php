<?php

namespace App\Services;

use App\Models\Harvest;
use App\Models\InvoicingSetting;
use App\Models\Tax;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceService
{
    /**
     * Atomically increment a per-user sequence and return the formatted invoice number
     * and delivery note code. Must be called inside an existing DB transaction.
     *
     * Example: generateSequentialNumber(1, 'harvest_sale_seq', 'HS-', 'VEN-')
     *          → ['number' => 'HS-2026-0001', 'noteCode' => 'VEN-2026-0001']
     */
    public function generateSequentialNumber(
        int $userId,
        string $seqColumn,
        string $numberPrefix,
        string $notePrefix,
    ): array {
        DB::table('users')->where('id', $userId)->lockForUpdate()->first();
        DB::table('users')->where('id', $userId)->increment($seqColumn);
        $seq = DB::table('users')->where('id', $userId)->value($seqColumn);
        $padded = str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
        $year = now()->year;

        return [
            'number'   => "{$numberPrefix}{$year}-{$padded}",
            'noteCode' => "{$notePrefix}{$year}-{$padded}",
        ];
    }

    /**
     * Calculate IRPF-style invoice totals where the tax is DEDUCTED from the payment
     * (vendor invoices: harvest_sale, grape_purchase).
     *
     * @param  array<int, array{quantity: string|float, unit_price: string|float, tax_rate: string|float}>  $lines
     * @return array{subtotal: float, tax_base: float, tax_amount: float, total: float}
     */
    public function calculateIrpfTotals(array $lines): array
    {
        $subtotal = 0.0;
        $taxAmount = 0.0;

        foreach ($lines as $line) {
            $sub = (float) $line['quantity'] * (float) $line['unit_price'];
            $subtotal += $sub;
            $taxAmount += $sub * ((float) $line['tax_rate'] / 100);
        }

        return [
            'subtotal'   => round($subtotal, 3),
            'tax_base'   => round($subtotal, 3),
            'tax_amount' => round($taxAmount, 3),
            'total'      => round($subtotal - $taxAmount, 3),
        ];
    }

    /**
     * Assert that a harvest (locked for update) belongs to the given viticulturist via
     * its activity relationship. Throws RuntimeException on failure.
     * Must be called inside an existing DB transaction.
     */
    public function validateViticulturistHarvestOwnership(int $harvestId, int $viticulturistId): Harvest
    {
        $harvest = Harvest::lockForUpdate()->find($harvestId);

        if (! $harvest) {
            throw new RuntimeException("Cosecha #{$harvestId} no encontrada.");
        }

        if ((int) optional($harvest->activity)->viticulturist_id !== $viticulturistId) {
            throw new RuntimeException("La cosecha #{$harvestId} no te pertenece.");
        }

        return $harvest;
    }

    /**
     * Assert that a harvest belongs to the winery, its batch to the given viticulturist,
     * and that it is not already included in another active invoice (double-invoicing guard).
     * Pass $excludeInvoiceId when editing to skip the current invoice in the check.
     * Must be called inside an existing DB transaction.
     */
    public function validateWineryHarvestOwnership(
        int $harvestId,
        int $wineryId,
        int $viticulturistId,
        ?int $excludeInvoiceId = null,
    ): Harvest {
        $harvest = Harvest::lockForUpdate()->find($harvestId);

        if (! $harvest || $harvest->winery_id !== $wineryId) {
            throw new RuntimeException("La recepción #{$harvestId} no pertenece a esta bodega.");
        }

        $harvest->loadMissing('batch');
        $isSelfPurchase = $wineryId === $viticulturistId;
        $batchViticulturistId = (string) ($harvest->batch?->viticulturist_id ?? '');
        $matches = $isSelfPurchase || $batchViticulturistId === (string) $viticulturistId;

        if (! $matches) {
            throw new RuntimeException("La recepción #{$harvest->id} no pertenece al viticultor seleccionado.");
        }

        $alreadyInvoiced = $harvest->invoiceItems()
            ->lockForUpdate()
            ->where('concept_type', 'harvest')
            ->whereHas('invoice', function ($q) use ($excludeInvoiceId) {
                $q->where('status', '!=', 'cancelled');
                if ($excludeInvoiceId) {
                    $q->where('id', '!=', $excludeInvoiceId);
                }
            })
            ->exists();

        if ($alreadyInvoiced) {
            throw new RuntimeException("La recepción #{$harvest->id} ya está incluida en otra liquidación activa.");
        }

        return $harvest;
    }

    /**
     * Calculate VAT-style invoice totals where the tax is ADDED to the net amount
     * (sales invoices: producer_sale, viticulturist sale).
     *
     * Accepts a Collection of Tax models keyed by their id so callers can pre-load
     * taxes in a single query and avoid N+1 lookups per item.
     *
     * Note on subtotal semantics: this method always returns `gross_subtotal` (before
     * discount) and `tax_base` (after discount) separately. Callers choose which value
     * to store as the invoice's `subtotal` column depending on their convention:
     *   - Producer: stores gross_subtotal as `subtotal`, tax_base as `tax_base`
     *   - Viticulturist: stores tax_base as both `subtotal` and `tax_base`
     *
     * @param  array<int, array{quantity: mixed, unit_price: mixed, discount_percentage?: mixed, tax_id?: mixed}>  $items
     * @param  Collection  $taxRatesById  Tax models keyed by id
     * @return array{gross_subtotal: float, discount_amount: float, tax_base: float, tax_amount: float, total: float, effective_tax_rate: float}
     */
    public function calculateVatTotals(array $items, Collection $taxRatesById): array
    {
        $grossSubtotal  = 0.0;
        $discountAmount = 0.0;
        $taxAmount      = 0.0;

        foreach ($items as $item) {
            $lineGross  = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $discPct    = (float) ($item['discount_percentage'] ?? 0);
            $lineDisc   = $lineGross * ($discPct / 100);
            $lineBase   = $lineGross - $lineDisc;
            $taxRate    = (float) ($taxRatesById->get($item['tax_id'] ?? null)?->rate ?? 0);

            $grossSubtotal  += $lineGross;
            $discountAmount += $lineDisc;
            $taxAmount      += $lineBase * ($taxRate / 100);
        }

        $taxBase = $grossSubtotal - $discountAmount;

        return [
            'gross_subtotal'     => round($grossSubtotal, 3),
            'discount_amount'    => round($discountAmount, 3),
            'tax_base'           => round($taxBase, 3),
            'tax_amount'         => round($taxAmount, 3),
            'total'              => round($taxBase + $taxAmount, 3),
            'effective_tax_rate' => $taxAmount > 0.0 && $taxBase > 0.0
                ? round(($taxAmount / $taxBase) * 100, 4)
                : 0.0,
        ];
    }

    /**
     * Return the delivery note code for a new invoice.
     * If the user has manually entered a code, use it as-is.
     * Otherwise atomically increment InvoicingSetting and return the generated code.
     * Must be called inside an existing DB transaction to stay atomic.
     */
    public function generateDeliveryNoteCode(int $userId, bool $isModified, string $manualCode): string
    {
        if ($isModified) {
            return $manualCode;
        }

        return InvoicingSetting::getOrCreateForUser($userId)->generateAndIncrementDeliveryNoteCode();
    }

    /**
     * Load the invoicing form data for producer/viticulturist invoice forms:
     * available taxes (user-configured or global fallback) and delivery note preview.
     *
     * @return array{taxes: Collection, defaultTax: Tax|null, settings: InvoicingSetting, deliveryNotePreview: string}
     */
    public function getInvoicingFormData(User $user): array
    {
        $taxes = $user->taxes()->orderByPivot('order')->get();

        if ($taxes->isEmpty()) {
            $taxes = Tax::active()->orderBy('rate')->get();
        }

        $defaultTax = $user->defaultTax()->first() ?? $taxes->first();
        $settings = InvoicingSetting::getOrCreateForUser($user->id);

        return [
            'taxes'               => $taxes,
            'defaultTax'          => $defaultTax,
            'settings'            => $settings,
            'deliveryNotePreview' => $settings->getDeliveryNotePreview(),
        ];
    }
}
