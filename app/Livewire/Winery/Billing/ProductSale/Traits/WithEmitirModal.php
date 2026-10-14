<?php

namespace App\Livewire\Winery\Billing\ProductSale\Traits;

use App\Models\Invoice;
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

        // Comprobación previa solo para feedback rápido — la comprobación que
        // realmente cuenta (con lockForUpdate) se hace DENTRO de la transacción
        // de abajo, para cerrar la ventana de doble-submit (ver auditoría fix #10).
        $invoice = $this->findInvoice($this->emitirId);
        if (! $invoice || $invoice->status !== 'draft') {
            $this->toastError(__('La factura ya no está en borrador.'));
            $this->closeEmitirModal();

            return;
        }

        $invoiceNumber = null;
        $emitirId = $this->emitirId;

        try {
            DB::transaction(function () use ($emitirId, &$invoiceNumber) {
                // Re-fetch con lockForUpdate() dentro de la transacción: si dos
                // submits concurrentes llegan aquí, el segundo espera a que el
                // primero confirme, relee status='sent' y aborta sin consumir
                // un número de más (evita huecos en la numeración correlativa).
                $locked = Invoice::where('id', $emitirId)
                    ->where('user_id', Auth::id())
                    ->where('invoice_type', 'wine_sale')
                    ->lockForUpdate()
                    ->first();

                if (! $locked || $locked->status !== 'draft') {
                    throw new \RuntimeException(__('La factura ya no está en borrador.'));
                }

                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $locked->update([
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
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al emitir la factura.'));
        }
    }
}
