<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait WithInvoiceStatusManagement
{
    public bool $showDeliveryModal = false;

    public string $pendingDeliveryStatus = '';

    public bool $showPaymentDateModal = false;

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
            // delivery_status is purely informational — stock is managed by invoice.status via
            // InvoiceObserver (draft→sent moves reserved→sold, cancelled releases all stock).
            // InvoiceObserver::handleDeliveryStatusChange() deliberately does NOT move stock.
            $this->invoice->update(['delivery_status' => $newStatus]);

            $this->delivery_status = $newStatus;
            $this->showDeliveryModal = false;
            $this->pendingDeliveryStatus = '';

            $this->persistPaymentStatus();

            $label = $newStatus === 'delivered' ? 'entregada' : 'cancelada';
            $this->toastSuccess("Estados guardados. Entrega: {$label}.");

        } catch (\Exception $e) {
            Log::error('Error al actualizar estado de entrega: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError(__('Error al actualizar el estado de entrega.'));
            $this->closeDeliveryModal();
        }
    }

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
