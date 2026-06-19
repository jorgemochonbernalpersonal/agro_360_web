<?php

namespace App\Livewire\Producer;

use App\Livewire\Concerns\WithSettingsFieldbook;
use App\Livewire\Concerns\WithSettingsFiscal;
use App\Livewire\Concerns\WithSettingsInvoicing;
use App\Livewire\Concerns\WithSettingsSignature;
use App\Livewire\Concerns\WithSettingsTaxes;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class Settings extends Component
{
    use WithSettingsFieldbook,
        WithSettingsFiscal,
        WithSettingsInvoicing,
        WithSettingsSignature,
        WithSettingsTaxes,
        WithToastNotifications;

    public $currentTab = 'taxes';

    public bool $compra_uva_externa = false;

    protected $queryString = ['currentTab' => ['as' => 'tab']];

    public function mount(): void
    {
        $this->compra_uva_externa = (bool) Auth::user()->compra_uva_externa;
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadFiscal();
        $this->loadFieldbook();
        $this->loadDigitalSignature();
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
    }

    public function toggleCompraUvaExterna(): void
    {
        $user = Auth::user();
        $user->update(['compra_uva_externa' => $this->compra_uva_externa]);
        Cache::forget("winery:{$user->id}:has_viticulturists");

        $this->toastSuccess($this->compra_uva_externa
            ? __('Compra de uva externa activada. Ya puedes gestionar viticultores.')
            : __('Compra de uva externa desactivada.'));
    }

    public function render()
    {
        return view('livewire.producer.settings')->layout('layouts.app', [
            'title' => __('Configuración - Agro365'),
            'description' => __('Gestiona la configuración de tu cuenta: impuestos, numeración de facturas, datos fiscales y cuaderno de campo.'),
        ]);
    }
}
