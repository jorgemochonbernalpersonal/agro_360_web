<?php

namespace App\Livewire\Viticulturist\Settings;

use App\Models\Tax;
use App\Models\UserTax;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use App\Livewire\Concerns\WithToastNotifications;

class Taxes extends Component
{
    use WithPagination, WithToastNotifications;

    public $selectedTaxes = [];
    public $search = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $user = Auth::user();
        // Cargar impuestos seleccionados por el usuario
        $this->selectedTaxes = UserTax::where('user_id', $user->id)
            ->pluck('tax_id')
            ->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function toggleTax($taxId)
    {
        $user = Auth::user();
        
        if (in_array($taxId, $this->selectedTaxes)) {
            // Desactivar
            UserTax::where('user_id', $user->id)
                ->where('tax_id', $taxId)
                ->delete();
            $this->selectedTaxes = array_values(array_diff($this->selectedTaxes, [$taxId]));
            $this->toastSuccess('Impuesto desactivado.');
        } else {
            // Activar
            UserTax::firstOrCreate([
                'user_id' => $user->id,
                'tax_id' => $taxId,
            ], [
                'is_default' => false,
                'order' => 0,
            ]);
            $this->selectedTaxes[] = $taxId;
            $this->toastSuccess('Impuesto activado.');
        }
    }

    public function setDefault($taxId)
    {
        $user = Auth::user();
        
        // Quitar default de todos
        UserTax::where('user_id', $user->id)
            ->update(['is_default' => false]);
        
        // Establecer este como default
        $userTax = UserTax::firstOrCreate([
            'user_id' => $user->id,
            'tax_id' => $taxId,
        ]);
        $userTax->update(['is_default' => true]);
        
        $this->toastSuccess('Impuesto por defecto actualizado.');
    }

    public function render()
    {
        $query = Tax::query()->active();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%')
                  ->orWhere('region', 'like', '%' . $this->search . '%');
            });
        }

        $taxes = $query->orderBy('region')
            ->orderBy('rate', 'desc')
            ->paginate(20);

        $user = Auth::user();
        $userTaxes = UserTax::where('user_id', $user->id)
            ->pluck('tax_id', 'tax_id')
            ->toArray();

        return view('livewire.viticulturist.settings.taxes', [
            'taxes' => $taxes,
            'userTaxes' => $userTaxes,
        ])->layout('layouts.app');
    }
}
