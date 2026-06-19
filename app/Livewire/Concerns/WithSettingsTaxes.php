<?php

namespace App\Livewire\Concerns;

use App\Models\Tax;
use App\Models\UserTax;
use Illuminate\Support\Facades\Auth;

trait WithSettingsTaxes
{
    public $taxes;

    public $activeTaxId;

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
            'user_id' => Auth::id(),
            'tax_id' => $taxId,
            'is_default' => true,
            'order' => 1,
        ]);
        $this->activeTaxId = $taxId;
        $this->toastSuccess(__('Impuesto configurado correctamente'));
    }
}
