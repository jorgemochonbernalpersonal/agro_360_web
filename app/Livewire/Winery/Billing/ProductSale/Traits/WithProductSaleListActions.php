<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\ProductLot;
use App\Services\ProductStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithProductSaleListActions
{
    public function markDelivered(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (! $invoice) {
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('No se puede marcar como entregada una factura cancelada.'));

            return;
        }

        if ($invoice->delivery_status === 'delivered') {
            $this->toastError(__('Esta factura ya está entregada.'));

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                ProductStockService::moveForInvoice($invoice, 'deliver');
                $invoice->update(['delivery_status' => 'delivered']);
            });

            $this->toastSuccess(__('Factura marcada como entregada. Stock movido a vendido.'));

        } catch (\Exception $e) {
            Log::error('Error al marcar factura como entregada: '.$e->getMessage(), [
                'invoice_id' => $invoiceId,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al procesar la entrega.'));
        }
    }

    public function cancel(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (! $invoice) {
            return;
        }

        if ($invoice->status === 'cancelled') {
            $this->toastError(__('Esta factura ya está cancelada.'));

            return;
        }

        if ($invoice->payment_status === 'paid') {
            $this->toastError(__('No se puede cancelar una factura ya cobrada.'));

            return;
        }

        if ($invoice->delivery_status === 'delivered') {
            $this->toastError(__('No se puede cancelar directamente un albarán entregado. Crea una factura rectificativa.'));

            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                ProductStockService::moveForInvoice($invoice, 'cancel');
                $invoice->update([
                    'status' => 'cancelled',
                    'delivery_status' => 'cancelled',
                ]);
            });

            $this->toastSuccess(__('Factura cancelada y stock restaurado.'));

        } catch (\Exception $e) {
            Log::error('Error al cancelar factura de productos: '.$e->getMessage(), [
                'invoice_id' => $invoiceId,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al cancelar la factura.'));
        }
    }

    public function duplicate(int $invoiceId): void
    {
        $original = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (! $original) {
            return;
        }

        try {
            DB::transaction(function () use ($original) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $noteCode = $settings->generateAndIncrementDeliveryNoteCode();

                $newInvoice = Invoice::create([
                    'user_id' => Auth::id(),
                    'client_id' => $original->client_id,
                    'client_address_id' => $original->client_address_id,
                    'invoice_type' => 'wine_sale',
                    'delivery_note_code' => $noteCode,
                    'delivery_note_date' => now()->toDateString(),
                    'order_date' => now()->toDateString(),
                    'invoice_date' => null,
                    'delivery_status' => 'pending',
                    'status' => 'draft',
                    'payment_status' => 'unpaid',
                    'payment_type' => $original->payment_type,
                    'billing_first_name' => $original->billing_first_name,
                    'billing_last_name' => $original->billing_last_name,
                    'billing_company_name' => $original->billing_company_name,
                    'billing_company_document' => $original->billing_company_document,
                    'billing_email' => $original->billing_email,
                    'billing_phone' => $original->billing_phone,
                    'billing_address' => $original->billing_address,
                    'billing_postal_code' => $original->billing_postal_code,
                    'billing_city' => $original->billing_city,
                    'billing_state' => $original->billing_state,
                    'billing_country' => $original->billing_country,
                    'gift' => $original->gift,
                    'tax_rate' => $original->tax_rate,
                    'subtotal' => $original->subtotal,
                    'discount_amount' => $original->discount_amount,
                    'tax_base' => $original->tax_base,
                    'tax_amount' => $original->tax_amount,
                    'total_amount' => $original->total_amount,
                    'observations' => $original->observations,
                    'observations_invoice' => $original->observations_invoice,
                ]);

                foreach ($original->items as $item) {
                    $lot = $item->wine_lot_id
                        ? ProductLot::where('user_id', Auth::id())->lockForUpdate()->find($item->wine_lot_id)
                        : null;

                    $createdItem = InvoiceItem::create([
                        'invoice_id' => $newInvoice->id,
                        'wine_lot_id' => $lot?->id,
                        'concept_type' => $item->concept_type,
                        'name' => $item->name,
                        'description' => $item->description,
                        'sku' => $item->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount' => $item->discount_amount,
                        'tax_id' => $item->tax_id,
                        'tax_name' => $item->tax_name,
                        'tax_rate' => $item->tax_rate,
                        'subtotal' => $item->subtotal,
                        'tax_base' => $item->tax_base,
                        'tax_amount' => $item->tax_amount,
                        'total' => $item->total,
                    ]);

                    if ($lot) {
                        ProductStockService::moveOnCreate($newInvoice, $createdItem, $lot, (float) $item->quantity);
                    }
                }
            });

            $this->toastSuccess(__('Albarán duplicado correctamente.'));

        } catch (\Exception $e) {
            Log::error('Error al duplicar factura: '.$e->getMessage(), ['invoice_id' => $invoiceId]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al duplicar el albarán.'));
        }
    }
}
