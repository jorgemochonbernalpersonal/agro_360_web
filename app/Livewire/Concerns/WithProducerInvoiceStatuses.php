<?php

namespace App\Livewire\Concerns;

use App\Models\InvoicingSetting;
use App\Services\UnifiedStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Status management for Producer\Invoices\Edit.
 *
 * Expects the using class to declare:
 *   public Invoice $invoice
 *   public string $delivery_status, $payment_status, $payment_type, $payment_date, $invoice_date
 */
trait WithProducerInvoiceStatuses
{
    public bool $showInvoiceModal = false;

    public string $invoice_date_modal = '';

    public bool $showDeliveryModal = false;

    public string $pendingDeliveryStatus = '';

    public bool $showPaymentDateModal = false;

    // ── Status save entry point ───────────────────────────────────────────────

    public function saveStatuses(): void
    {
        if ($this->invoice->status === 'cancelled') {
            $this->toastError(__('No se puede modificar una factura cancelada.'));

            return;
        }

        if ($this->payment_status === 'paid' && ! $this->payment_date) {
            $this->showPaymentDateModal = true;

            return;
        }

        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal = true;

            return;
        }

        $this->persistStatuses();
    }

    // ── Payment date modal ────────────────────────────────────────────────────

    public function confirmPaymentDate(): void
    {
        $this->validate(
            ['payment_date' => 'required|date'],
            ['payment_date.required' => __('La fecha de cobro es obligatoria.')]
        );

        $this->showPaymentDateModal = false;

        $originalDelivery = $this->invoice->delivery_status;
        if ($this->delivery_status !== $originalDelivery
            && in_array($this->delivery_status, ['delivered', 'cancelled'])) {
            $this->pendingDeliveryStatus = $this->delivery_status;
            $this->showDeliveryModal = true;

            return;
        }

        $this->persistStatuses();
    }

    public function closePaymentDateModal(): void
    {
        $this->showPaymentDateModal = false;
        $this->resetValidation('payment_date');
    }

    // ── Delivery confirmation modal ───────────────────────────────────────────

    public function closeDeliveryModal(): void
    {
        $this->delivery_status = $this->invoice->delivery_status;
        $this->showDeliveryModal = false;
        $this->pendingDeliveryStatus = '';
    }

    public function confirmDeliveryStatus(): void
    {
        $newStatus = $this->pendingDeliveryStatus;

        if (! in_array($newStatus, ['delivered', 'cancelled'])) {
            $this->closeDeliveryModal();

            return;
        }

        try {
            DB::transaction(function () use ($newStatus) {
                $this->invoice->load('items.harvest', 'items.wineLot');

                $stockService = app(UnifiedStockService::class);

                if (! $this->invoice->corrective) {
                    if ($newStatus === 'delivered') {
                        $stockService->confirmDelivery($this->invoice);
                    } else {
                        $stockService->cancelDelivery($this->invoice);
                    }
                }

                $this->invoice->update(['delivery_status' => $newStatus]);
            });

            $this->delivery_status = $newStatus;
            $this->showDeliveryModal = false;
            $this->pendingDeliveryStatus = '';

            $this->persistPaymentStatus();

            $label = $newStatus === 'delivered' ? 'entregada' : 'cancelada';
            $this->toastSuccess("Estados guardados. Entrega: {$label}.");

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de entrega (producer): '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException
                ? $e->getMessage()
                : __('Error al actualizar el estado de entrega.'));
            $this->closeDeliveryModal();
        }
    }

    // ── Invoice emission modal ────────────────────────────────────────────────

    public function openInvoiceModal(): void
    {
        if ($this->invoice->status !== 'draft') {
            $this->toastError(__('Solo se puede facturar un albarán en estado borrador.'));

            return;
        }

        $this->invoice_date_modal = $this->invoice_date ?: now()->toDateString();
        $this->showInvoiceModal = true;
    }

    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
        $this->invoice_date_modal = '';
    }

    public function markAsSent(): void
    {
        $this->validate(
            ['invoice_date_modal' => 'required|date'],
            ['invoice_date_modal.required' => __('Debes indicar la fecha de la factura.')]
        );

        try {
            DB::transaction(function () {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());

                $this->invoice->update([
                    'status' => 'sent',
                    'invoice_date' => $this->invoice_date_modal,
                    'invoice_number' => $settings->generateAndIncrementInvoiceCode(),
                ]);
            });

            $this->toastSuccess(__('Factura emitida correctamente.'));
            $this->closeInvoiceModal();
            $this->redirect(route('producer.invoices.mixed.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al emitir factura de productor: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException
                ? $e->getMessage()
                : __('Error al facturar. Inténtalo de nuevo.'));
        }
    }

    // ── Persistence helpers ───────────────────────────────────────────────────

    private function persistStatuses(): void
    {
        $this->invoice->update([
            'delivery_status' => $this->delivery_status,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type ?: null,
            'payment_date' => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
        $this->toastSuccess(__('Estados actualizados correctamente.'));
    }

    private function persistPaymentStatus(): void
    {
        $this->invoice->update([
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type ?: null,
            'payment_date' => $this->payment_status === 'paid' ? ($this->payment_date ?: null) : null,
        ]);
        $this->invoice->refresh();
    }
}
