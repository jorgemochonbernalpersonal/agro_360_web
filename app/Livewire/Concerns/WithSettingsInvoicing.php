<?php

namespace App\Livewire\Concerns;

use App\Models\InvoicingSetting;
use Illuminate\Support\Facades\Auth;

trait WithSettingsInvoicing
{
    public $invoice_prefix;

    public $invoice_padding;

    public $invoice_counter;

    public $invoice_year_reset;

    public $delivery_note_prefix;

    public $delivery_note_padding;

    public $delivery_note_counter;

    public $delivery_note_year_reset;

    public $invoicePreview;

    public $deliveryNotePreview;

    public function loadInvoicing(): void
    {
        $settings = InvoicingSetting::forUser(Auth::id())->first()
            ?? InvoicingSetting::createDefaultForUser(Auth::id());

        $this->invoice_prefix = $settings->invoice_prefix;
        $this->invoice_padding = $settings->invoice_padding;
        $this->invoice_counter = $settings->invoice_counter;
        $this->invoice_year_reset = $settings->invoice_year_reset;
        $this->delivery_note_prefix = $settings->delivery_note_prefix;
        $this->delivery_note_padding = $settings->delivery_note_padding;
        $this->delivery_note_counter = $settings->delivery_note_counter;
        $this->delivery_note_year_reset = $settings->delivery_note_year_reset;

        $this->updatePreviews();
    }

    public function updated($propertyName): void
    {
        if (str_starts_with($propertyName, 'invoice_') || str_starts_with($propertyName, 'delivery_note_')) {
            $this->updatePreviews();
        }
    }

    public function updatePreviews(): void
    {
        $this->invoicePreview = $this->replaceVariables($this->invoice_prefix)
            .str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);
        $this->deliveryNotePreview = $this->replaceVariables($this->delivery_note_prefix)
            .str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
    }

    public function saveInvoicing(): void
    {
        $this->validate([
            'invoice_prefix' => 'required|string|max:50',
            'invoice_padding' => 'required|integer|min:2|max:6',
            'invoice_counter' => 'required|integer|min:1',
            'delivery_note_prefix' => 'required|string|max:50',
            'delivery_note_padding' => 'required|integer|min:2|max:6',
            'delivery_note_counter' => 'required|integer|min:1',
        ]);

        InvoicingSetting::forUser(Auth::id())->first()->update([
            'invoice_prefix' => $this->invoice_prefix,
            'invoice_padding' => $this->invoice_padding,
            'invoice_counter' => $this->invoice_counter,
            'invoice_year_reset' => $this->invoice_year_reset,
            'delivery_note_prefix' => $this->delivery_note_prefix,
            'delivery_note_padding' => $this->delivery_note_padding,
            'delivery_note_counter' => $this->delivery_note_counter,
            'delivery_note_year_reset' => $this->delivery_note_year_reset,
        ]);

        $this->updatePreviews();
        $this->toastSuccess(__('Configuración guardada correctamente'));
    }

    public function resetInvoiceCounter(): void
    {
        $this->invoice_counter = 1;
        $this->updatePreviews();
        $this->toastInfo(__('Contador de facturas reseteado. Haz clic en Guardar para aplicar.'));
    }

    public function resetDeliveryNoteCounter(): void
    {
        $this->delivery_note_counter = 1;
        $this->updatePreviews();
        $this->toastInfo(__('Contador de albaranes reseteado. Haz clic en Guardar para aplicar.'));
    }

    protected function replaceVariables(string $prefix): string
    {
        $now = now();

        return str_replace(
            ['{YEAR}', '{MONTH}', '{DAY}'],
            [$now->format('Y'), $now->format('m'), $now->format('d')],
            $prefix
        );
    }
}
