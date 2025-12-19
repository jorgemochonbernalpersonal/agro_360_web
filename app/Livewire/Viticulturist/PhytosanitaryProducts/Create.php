<?php

namespace App\Livewire\Viticulturist\PhytosanitaryProducts;

use App\Models\PhytosanitaryProduct;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;
    public $name = '';
    public $active_ingredient = '';
    public $registration_number = '';
    public $manufacturer = '';
    public $type = '';
    public $toxicity_class = '';
    public $withdrawal_period_days = '';
    public $description = '';

    public function mount(): void
    {
        if (! Auth::user()->isViticulturist()) {
            abort(403, 'No tienes permiso para crear productos fitosanitarios.');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'registration_number' => 'nullable|string|max:100',
            'manufacturer' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'toxicity_class' => 'nullable|string|max:20',
            'withdrawal_period_days' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                PhytosanitaryProduct::create([
                    'name' => $this->name,
                    'active_ingredient' => $this->active_ingredient ?: null,
                    'registration_number' => $this->registration_number ?: null,
                    'manufacturer' => $this->manufacturer ?: null,
                    'type' => $this->type ?: null,
                    'toxicity_class' => $this->toxicity_class ?: null,
                    'withdrawal_period_days' => $this->withdrawal_period_days ?: null,
                    'description' => $this->description ?: null,
                ]);
            });

            $this->toastSuccess('Producto fitosanitario creado correctamente.');

            return redirect()->route('viticulturist.phytosanitary-products.index');
        } catch (\Exception $e) {
            \Log::error('Error al crear producto fitosanitario', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al crear el producto fitosanitario. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.phytosanitary-products.create')
            ->layout('layouts.app');
    }
}


