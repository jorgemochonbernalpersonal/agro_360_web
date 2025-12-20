<?php

namespace App\Livewire\Viticulturist;

use App\Models\Tax;
use App\Models\UserTax;
use App\Models\InvoicingSetting;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithToastNotifications;

class Settings extends Component
{
    use WithToastNotifications;

    public $currentTab = 'taxes';
    
    protected $queryString = ['currentTab' => ['as' => 'tab']];

    // === TAXES TAB ===
    public $taxes;
    public $activeTaxId;

    // === INVOICING TAB ===
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

    public function mount()
    {
        $this->loadTaxes();
        $this->loadInvoicing();
    }

    public function switchTab($tab)
    {
        $this->currentTab = $tab;
    }

    // ==========================================
    // TAXES TAB METHODS
    // ==========================================

    public function loadTaxes()
    {
        $user = Auth::user();
        
        // Obtener todos los impuestos disponibles
        $this->taxes = Tax::orderBy('rate', 'asc')->get();
        
        // Obtener el impuesto activo del usuario
        $userTax = UserTax::where('user_id', $user->id)->first();
        $this->activeTaxId = $userTax?->tax_id;
    }

    public function selectTax($taxId)
    {
        $user = Auth::user();
        
        // Eliminar impuesto anterior
        UserTax::where('user_id', $user->id)->delete();
        
        // Crear nuevo
        UserTax::create([
            'user_id' => $user->id,
            'tax_id' => $taxId,
            'is_default' => true,
            'order' => 1
        ]);
        
        $this->activeTaxId = $taxId;
        $this->toastSuccess('Impuesto configurado correctamente');
    }

    // ==========================================
    // INVOICING TAB METHODS
    // ==========================================

    public function loadInvoicing()
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
        if (str_starts_with($propertyName, 'invoice_') || str_starts_with($propertyName, 'delivery_note_')) {
            $this->updatePreviews();
        }
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

    public function saveInvoicing()
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
        return view('livewire.viticulturist.settings')->layout('layouts.app');
    }
}
