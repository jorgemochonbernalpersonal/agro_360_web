<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithCorrectiveModal
{
    public bool $correctiveModal = false;

    public ?int $correctiveId = null;

    public string $correctiveDate = '';

    public string $correctiveReason = '';

    public function openCorrectiveModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'sent') {
            $this->toastError(__('Solo se puede rectificar una factura emitida.'));

            return;
        }

        if ($invoice->corrective) {
            $this->toastError(__('Una rectificativa no puede rectificarse a sí misma.'));

            return;
        }

        if (Invoice::where('corrected_invoice_id', $id)->exists()) {
            $this->toastError(__('Esta factura ya tiene una rectificativa asociada.'));

            return;
        }

        $this->correctiveId = $id;
        $this->correctiveDate = now()->toDateString();
        $this->correctiveReason = '';
        $this->correctiveModal = true;
    }

    public function closeCorrectiveModal(): void
    {
        $this->correctiveModal = false;
        $this->correctiveId = null;
        $this->correctiveDate = '';
        $this->correctiveReason = '';
        $this->resetValidation();
    }

    public function confirmCorrective(): void
    {
        $this->validate(
            [
                'correctiveDate' => 'required|date',
                'correctiveReason' => 'nullable|string|max:500',
            ],
            ['correctiveDate.required' => __('La fecha de la rectificativa es obligatoria.')]
        );

        $original = $this->findInvoice($this->correctiveId, ['items.wineLot']);
        if (! $original || $original->status !== 'sent') {
            $this->toastError(__('La factura original ya no es válida para rectificar.'));
            $this->closeCorrectiveModal();

            return;
        }

        if ((int) $original->user_id !== Auth::id()) {
            $this->toastError(__('No tienes permiso para rectificar esta factura.'));
            $this->closeCorrectiveModal();

            return;
        }

        if (Invoice::where('corrected_invoice_id', $original->id)->exists()) {
            $this->toastError(__('Esta factura ya tiene una rectificativa asociada.'));
            $this->closeCorrectiveModal();

            return;
        }

        $invoiceNumber = null;

        try {
            DB::transaction(function () use ($original, &$invoiceNumber) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $notes = 'Rectificativa de '.$original->invoice_number.'.'
                    .($this->correctiveReason ? ' Motivo: '.$this->correctiveReason : '');

                $corrective = Invoice::withoutEvents(fn () => Invoice::create([
                    'user_id' => Auth::id(),
                    'client_id' => $original->client_id,
                    'client_address_id' => $original->client_address_id,
                    'corrected_invoice_id' => $original->id,
                    'invoice_type' => 'wine_sale',
                    'corrective' => true,
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $this->correctiveDate,
                    'order_date' => now(),
                    'status' => 'sent',
                    'payment_status' => 'unpaid',
                    'delivery_status' => 'cancelled',
                    'subtotal' => -abs((float) $original->subtotal),
                    'discount_amount' => -abs((float) $original->discount_amount),
                    'tax_base' => -abs((float) $original->tax_base),
                    'tax_rate' => $original->tax_rate,
                    'tax_amount' => -abs((float) $original->tax_amount),
                    'total_amount' => -abs((float) $original->total_amount),
                    'billing_first_name' => $original->billing_first_name,
                    'billing_last_name' => $original->billing_last_name,
                    'billing_email' => $original->billing_email,
                    'billing_phone' => $original->billing_phone,
                    'billing_company_name' => $original->billing_company_name,
                    'billing_company_document' => $original->billing_company_document,
                    'billing_address' => $original->billing_address,
                    'billing_postal_code' => $original->billing_postal_code,
                    'billing_city' => $original->billing_city,
                    'billing_state' => $original->billing_state,
                    'billing_country' => $original->billing_country,
                    'observations' => $notes,
                ]));

                InvoiceItem::withoutEvents(function () use ($original, $corrective) {
                    foreach ($original->items as $item) {
                        $corrective->items()->create([
                            'wine_lot_id' => $item->wine_lot_id,
                            'harvest_id' => $item->harvest_id,
                            'concept_type' => $item->concept_type,
                            'name' => $item->name,
                            'description' => $item->description,
                            'sku' => $item->sku,
                            'quantity' => -(float) $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount_percentage' => $item->discount_percentage,
                            'discount_amount' => -(float) $item->discount_amount,
                            'tax_id' => $item->tax_id,
                            'tax_name' => $item->tax_name,
                            'tax_rate' => $item->tax_rate,
                            'tax_base' => -(float) $item->tax_base,
                            'tax_amount' => -(float) $item->tax_amount,
                            'subtotal' => -(float) $item->subtotal,
                            'total' => -(float) $item->total,
                        ]);
                    }
                });

                ProductStockService::moveForInvoice($original, 'cancel');
            });

            $this->closeCorrectiveModal();
            $this->toastSuccess("Rectificativa {$invoiceNumber} emitida. Stock restaurado.");

        } catch (\Exception $e) {
            Log::error('Error al crear rectificativa de venta de productos: '.$e->getMessage(), [
                'original_invoice_id' => $this->correctiveId,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al generar la rectificativa.'));
        }
    }
}
