<?php

namespace App\Livewire\Winery;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\InvoicingSetting;
use App\Models\Organization;
use App\Models\Tax;
use App\Models\UserProfile;
use App\Models\UserTax;
use App\Models\ViticulturistSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Settings extends Component
{
    use WithToastNotifications;

    public $currentTab = 'taxes';

    // === INFOVI TAB ===
    public string $reovi_number = '';
    public string $nidpb        = '';

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

    // === FISCAL TAB ===
    public $fiscal_nif          = '';
    public $fiscal_legal_name   = '';
    public $fiscal_address      = '';
    public $fiscal_city         = '';
    public $fiscal_postal_code  = '';
    public $fiscal_phone        = '';

    public function mount(): void
    {
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadPlots();
        $this->loadFiscal();
        $this->loadInfovi();
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

    // ==========================================
    // FISCAL
    // ==========================================

    public function loadFiscal(): void
    {
        $user    = Auth::user();
        $profile = UserProfile::where('user_id', $user->id)->first();
        $inv     = InvoicingSetting::forUser($user->id)->first();
        $org     = $user->organization;

        $this->fiscal_nif         = $user->dni ?? $org?->vat_number ?? '';
        $this->fiscal_legal_name  = $inv?->issuer_legal_name ?? '';
        $this->fiscal_address     = $profile?->address ?? $org?->address ?? '';
        $this->fiscal_city        = $profile?->city ?? $org?->city ?? '';
        $this->fiscal_postal_code = $profile?->postal_code ?? $org?->postal_code ?? '';
        $this->fiscal_phone       = $profile?->phone ?? $org?->phone ?? '';
    }

    public function saveFiscal(): void
    {
        $this->validate([
            'fiscal_nif'         => 'nullable|string|max:20',
            'fiscal_legal_name'  => 'nullable|string|max:150',
            'fiscal_address'     => 'nullable|string|max:255',
            'fiscal_city'        => 'nullable|string|max:100',
            'fiscal_postal_code' => 'nullable|string|max:10',
            'fiscal_phone'       => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        // Update NIF on user record
        $user->update(['dni' => $this->fiscal_nif ?: null]);

        // Update issuer_legal_name on invoicing settings
        $inv = InvoicingSetting::forUser($user->id)->first()
            ?? InvoicingSetting::createDefaultForUser($user->id);
        $inv->update(['issuer_legal_name' => $this->fiscal_legal_name ?: null]);

        // Update profile (address, city, postal_code, phone)
        UserProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'address'     => $this->fiscal_address ?: null,
                'city'        => $this->fiscal_city ?: null,
                'postal_code' => $this->fiscal_postal_code ?: null,
                'phone'       => $this->fiscal_phone ?: null,
            ]
        );

        // Sync to Organization record if this user has one
        $user->organization?->update([
            'name'        => $this->fiscal_legal_name ?: $user->name,
            'vat_number'  => $this->fiscal_nif ?: null,
            'address'     => $this->fiscal_address ?: null,
            'city'        => $this->fiscal_city ?: null,
            'postal_code' => $this->fiscal_postal_code ?: null,
            'phone'       => $this->fiscal_phone ?: null,
            'email'       => !str_contains($user->email, '@noemail.agro365.es') ? $user->email : null,
        ]);

        $this->toastSuccess('Datos fiscales guardados correctamente');
    }

    // ==========================================
    // INFOVI
    // ==========================================

    public function loadInfovi(): void
    {
        $org = Auth::user()->organization;
        $this->reovi_number = $org?->reovi_number ?? '';
        $this->nidpb        = $org?->nidpb ?? '';
    }

    public function saveInfovi(): void
    {
        $this->validate([
            'reovi_number' => 'nullable|string|max:50',
            'nidpb'        => 'nullable|string|max:50',
        ]);

        Auth::user()->organization?->update([
            'reovi_number' => $this->reovi_number ?: null,
            'nidpb'        => $this->nidpb ?: null,
        ]);

        $this->toastSuccess('Configuración INFOVI guardada correctamente');
    }

    public function render()
    {
        return view('livewire.winery.settings')->layout('layouts.app', [
            'title'       => 'Configuración - Agro365',
            'description' => 'Gestiona la configuración de tu cuenta de bodega.',
        ]);
    }
}
