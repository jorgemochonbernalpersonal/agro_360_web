<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

use App\Models\InvoicingSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait WithEmitirModal
{
    public bool $emitirModal = false;

    public ?int $emitirId = null;

    public string $emitirDate = '';

    public function openEmitirModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (! $invoice) {
            return;
        }

        if ($invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede emitir una factura en borrador.'));

            return;
        }

        $this->emitirId = $id;
        $this->emitirDate = now()->toDateString();
        $this->emitirModal = true;
    }

    public function closeEmitirModal(): void
    {
        $this->emitirModal = false;
        $this->emitirId = null;
        $this->emitirDate = '';
        $this->resetValidation();
    }

    public function confirmEmitir(): void
    {
        $this->validate(
            ['emitirDate' => 'required|date'],
            ['emitirDate.required' => __('La fecha de factura es obligatoria.')]
        );

        $invoice = $this->findInvoice($this->emitirId);
        if (! $invoice || $invoice->status !== 'draft') {
            $this->toastError(__('La factura ya no está en borrador.'));
            $this->closeEmitirModal();

            return;
        }

        $invoiceNumber = null;

        try {
            DB::transaction(function () use ($invoice, &$invoiceNumber) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $invoice->update([
                    'invoice_number' => $invoiceNumber,
                    'invoice_date' => $this->emitirDate,
                    'status' => 'sent',
                ]);
            });

            $this->closeEmitirModal();
            $this->toastSuccess("Factura {$invoiceNumber} emitida correctamente.");

        } catch (\Exception $e) {
            Log::error('Error al emitir factura de productos: '.$e->getMessage(), [
                'invoice_id' => $this->emitirId,
                'user_id' => Auth::id(),
            ]);
            $this->toastError(__('Error al emitir la factura.'));
        }
    }
}
