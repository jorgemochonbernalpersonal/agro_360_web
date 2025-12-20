<?php

namespace App\Livewire\Viticulturist\Settings;

use App\Models\InvoicingSetting;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithToastNotifications;

class Invoicing extends Component
{
    use WithToastNotifications;

    // Facturas
    public $invoice_prefix;
    public $invoice_padding;
    public $invoice_counter;
    public $invoice_year_reset;

    // Albaranes
    public $delivery_note_prefix;
    public $delivery_note_padding;
    public $delivery_note_counter;
    public $delivery_note_year_reset;

    // Previews
    public $invoicePreview;
    public $deliveryNotePreview;

    public function mount()
    {
        $user = Auth::user();
        
        // Obtener o crear configuración
        $settings = InvoicingSetting::forUser($user->id)->first();
        
        if (!$settings) {
            $settings = InvoicingSetting::createDefaultForUser($user->id);
        }

        // Cargar valores
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

    public function updated($propertyName)
    {
        $this->updatePreviews();
    }

    public function updatePreviews()
    {
        $this->invoicePreview = $this->replaceVariables($this->invoice_prefix) . 
                                str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);

        $this->deliveryNotePreview = $this->replaceVariables($this->delivery_note_prefix) . 
                                     str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
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

    public function save()
    {
        $this->validate([
            'invoice_prefix' => 'required|string|max:50',
            'invoice_padding' => 'required|integer|min:2|max:6',
            'invoice_counter' => 'required|integer|min:1',
            'delivery_note_prefix' => 'required|string|max:50',
            'delivery_note_padding' => 'required|integer|min:2|max:6',
            'delivery_note_counter' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $settings = InvoicingSetting::forUser($user->id)->first();

        $settings->update([
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
        $this->toastSuccess('Configuración guardada exitosamente');
    }

    public function resetInvoiceCounter()
    {
        $this->invoice_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de facturas resetado. Haz clic en Guardar para aplicar.');
    }

    public function resetDeliveryNoteCounter()
    {
        $this->delivery_note_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de albaranes resetado. Haz clic en Guardar para aplicar.');
    }

    public function render()
    {
        return view('livewire.viticulturist.settings.invoicing')->layout('layouts.app');
    }
}
