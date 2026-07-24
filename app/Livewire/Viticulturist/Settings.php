<?php

namespace App\Livewire\Viticulturist;

use App\Livewire\Concerns\WithSettingsFieldbook;
use App\Livewire\Concerns\WithSettingsFiscal;
use App\Livewire\Concerns\WithSettingsInvoicing;
use App\Livewire\Concerns\WithSettingsSignature;
use App\Livewire\Concerns\WithSettingsTaxes;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithVerifactuCertificate;
use Livewire\Component;

class Settings extends Component
{
    use WithSettingsFieldbook, WithSettingsFiscal, WithSettingsInvoicing, WithSettingsSignature, WithSettingsTaxes, WithToastNotifications, WithVerifactuCertificate;

    public $currentTab = 'taxes';

    protected $queryString = ['currentTab' => ['as' => 'tab']];

    protected $listeners = ['signature-updated' => 'refreshSignatureStatus'];

    public function mount(): void
    {
        $this->loadTaxes();
        $this->loadInvoicing();
        $this->loadFiscal();
        $this->loadDigitalSignature();
        $this->loadFieldbook();
        $this->loadVerifactuCertificate();
    }

    public function switchTab($tab): void
    {
        $this->currentTab = $tab;
    }

    public function refreshSignatureStatus(): void
    {
        $this->loadDigitalSignature();
    }

    public function render()
    {
        return view('livewire.viticulturist.settings')->layout('layouts.app', [
            'title' => __('Configuración - Agro365'),
            'description' => __('Gestiona la configuración de tu cuenta: impuestos, numeración de facturas y albaranes, y preferencias de facturación.'),
        ]);
    }
}
