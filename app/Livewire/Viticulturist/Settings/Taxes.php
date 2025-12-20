<?php

namespace App\Livewire\Viticulturist\Settings;

use App\Models\Tax;
use App\Models\UserTax;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Livewire\Concerns\WithToastNotifications;

class Taxes extends Component
{
    use WithToastNotifications;

    public $activeTaxId = null; // Solo uno activo a la vez

    public function mount()
    {
        $user = Auth::user();
        
        // Obtener el impuesto activo (solo puede haber 1)
        $activeTax = UserTax::where('user_id', $user->id)
            ->where('is_default', true)
            ->first();

        $this->activeTaxId = $activeTax?->tax_id;
    }

    /**
     * Seleccionar impuesto (solo uno a la vez)
     */
    public function selectTax($taxId)
    {
        $user = Auth::user();

        // Eliminar TODOS los impuestos del usuario
        UserTax::where('user_id', $user->id)->delete();

        // Crear el nuevo impuesto como default
        UserTax::create([
            'user_id' => $user->id,
            'tax_id' => $taxId,
            'is_default' => true,
            'order' => 0,
        ]);

        $this->activeTaxId = $taxId;

        $tax = Tax::find($taxId);
        $this->toastSuccess("Impuesto configurado: {$tax->name}");
    }

    public function render()
    {
        // Obtener los 3 impuestos principales
        $taxes = Tax::active()
            ->whereIn('code', ['EXENTO', 'IVA', 'IGIC'])
            ->orderByRaw("FIELD(code, 'EXENTO', 'IVA', 'IGIC')")
            ->get();

        return view('livewire.viticulturist.settings.taxes', [
            'taxes' => $taxes,
        ])->layout('layouts.app');
    }
}
