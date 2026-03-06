<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\InvoicingSetting;
use App\Models\Tax;
use App\Models\UserTax;
use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

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

    // === PLOTS TAB ===
    public $default_limit_kg_per_ha = '';

    public function mount(): void
    {
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadPlots();
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
    }

    // ==========================================
    // TAXES
    // ==========================================

    public function loadTaxes(): void
    {
        $this->taxes = Tax::orderBy('rate', 'asc')->get();
        $userTax = UserTax::where('user_id', Auth::id())->first();
        $this->activeTaxId = $userTax?->tax_id;
    }

    public function selectTax($taxId): void
    {
        UserTax::where('user_id', Auth::id())->delete();
        UserTax::create([
            'user_id'    => Auth::id(),
            'tax_id'     => $taxId,
            'is_default' => true,
            'order'      => 1,
        ]);
        $this->activeTaxId = $taxId;
        $this->toastSuccess('Impuesto configurado correctamente');
    }

    // ==========================================
    // INVOICING
    // ==========================================

    public function loadInvoicing(): void
    {
        $settings = InvoicingSetting::forUser(Auth::id())->first()
            ?? InvoicingSetting::createDefaultForUser(Auth::id());

        $this->invoice_prefix            = $settings->invoice_prefix;
        $this->invoice_padding           = $settings->invoice_padding;
        $this->invoice_counter           = $settings->invoice_counter;
        $this->invoice_year_reset        = $settings->invoice_year_reset;
        $this->delivery_note_prefix      = $settings->delivery_note_prefix;
        $this->delivery_note_padding     = $settings->delivery_note_padding;
        $this->delivery_note_counter     = $settings->delivery_note_counter;
        $this->delivery_note_year_reset  = $settings->delivery_note_year_reset;

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
        $this->invoicePreview      = $this->replaceVariables($this->invoice_prefix)
            . str_pad($this->invoice_counter, $this->invoice_padding, '0', STR_PAD_LEFT);
        $this->deliveryNotePreview = $this->replaceVariables($this->delivery_note_prefix)
            . str_pad($this->delivery_note_counter, $this->delivery_note_padding, '0', STR_PAD_LEFT);
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

    public function saveInvoicing(): void
    {
        $this->validate([
            'invoice_prefix'           => 'required|string|max:50',
            'invoice_padding'          => 'required|integer|min:2|max:6',
            'invoice_counter'          => 'required|integer|min:1',
            'delivery_note_prefix'     => 'required|string|max:50',
            'delivery_note_padding'    => 'required|integer|min:2|max:6',
            'delivery_note_counter'    => 'required|integer|min:1',
        ]);

        $settings = InvoicingSetting::forUser(Auth::id())->first();
        $settings->update([
            'invoice_prefix'           => $this->invoice_prefix,
            'invoice_padding'          => $this->invoice_padding,
            'invoice_counter'          => $this->invoice_counter,
            'invoice_year_reset'       => $this->invoice_year_reset,
            'delivery_note_prefix'     => $this->delivery_note_prefix,
            'delivery_note_padding'    => $this->delivery_note_padding,
            'delivery_note_counter'    => $this->delivery_note_counter,
            'delivery_note_year_reset' => $this->delivery_note_year_reset,
        ]);

        $this->updatePreviews();
        $this->toastSuccess('Configuración guardada correctamente');
    }

    public function resetInvoiceCounter(): void
    {
        $this->invoice_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de facturas reseteado. Haz clic en Guardar para aplicar.');
    }

    public function resetDeliveryNoteCounter(): void
    {
        $this->delivery_note_counter = 1;
        $this->updatePreviews();
        $this->toastInfo('Contador de albaranes reseteado. Haz clic en Guardar para aplicar.');
    }

    // ==========================================
    // PLOTS
    // ==========================================

    public function loadPlots(): void
    {
        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $this->default_limit_kg_per_ha = $settings->default_limit_kg_per_ha ?? '';
    }

    public function savePlots(): void
    {
        $this->validate([
            'default_limit_kg_per_ha' => 'nullable|numeric|min:0|max:999999',
        ]);

        $settings = ViticulturistSetting::forUser(Auth::id())
            ?? ViticulturistSetting::createDefaultForUser(Auth::id());

        $settings->update([
            'default_limit_kg_per_ha' => $this->default_limit_kg_per_ha ?: null,
        ]);

        $this->toastSuccess('Configuración de parcelas guardada correctamente');
    }

    public function render()
    {
        return view('livewire.winery.settings')->layout('layouts.app', [
            'title'       => 'Configuración - Agro365',
            'description' => 'Gestiona la configuración de tu cuenta de bodega.',
        ]);
    }
}
