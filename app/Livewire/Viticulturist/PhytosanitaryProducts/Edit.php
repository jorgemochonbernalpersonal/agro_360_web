<?php

namespace App\Livewire\Viticulturist\PhytosanitaryProducts;

use App\Models\PhytosanitaryProduct;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;
    public PhytosanitaryProduct $product;

    public $name = '';
    public $active_ingredient = '';
    public $registration_number = '';
    public $registration_expiry_date = '';
    public $registration_status = 'active';
    public $manufacturer = '';
    public $type = '';
    public $toxicity_class = '';
    public $withdrawal_period_days = 0;
    public $description = '';

    public function mount(PhytosanitaryProduct $product): void
    {
        if (! Auth::user()->isViticulturist()) {
            abort(403, 'No tienes permiso para editar productos fitosanitarios.');
        }

        $this->product = $product;

        $this->name = $product->name;
        $this->active_ingredient = $product->active_ingredient ?? '';
        $this->registration_number = $product->registration_number ?? '';
        $this->registration_expiry_date = $product->registration_expiry_date ? $product->registration_expiry_date->format('Y-m-d') : '';
        $this->registration_status = $product->registration_status ?? 'active';
        $this->manufacturer = $product->manufacturer ?? '';
        $this->type = $product->type ?? '';
        $this->toxicity_class = $product->toxicity_class ?? '';
        $this->withdrawal_period_days = $product->withdrawal_period_days ?? 0;
        $this->description = $product->description ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'registration_number' => ['required', 'string', 'regex:/^ES-\d{8}$/'],
            'registration_expiry_date' => 'nullable|date|after:today',
            'registration_status' => 'required|string|in:active,expired,revoked',
            'manufacturer' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:herbicida,fungicida,insecticida,acaricida,nematicida,otro',
            'toxicity_class' => 'nullable|string|in:I,II,III,IV',
            'withdrawal_period_days' => 'required|integer|min:0',
            'description' => 'nullable|string',
        ];
    }

    public function save()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $this->product->update([
                    'name' => $this->name,
                    'active_ingredient' => $this->active_ingredient ?: null,
                    'registration_number' => $this->registration_number,
                    'registration_expiry_date' => $this->registration_expiry_date ?: null,
                    'registration_status' => $this->registration_status,
                    'manufacturer' => $this->manufacturer ?: null,
                    'type' => $this->type ?: null,
                    'toxicity_class' => $this->toxicity_class ?: null,
                    'withdrawal_period_days' => $this->withdrawal_period_days,
                    'description' => $this->description ?: null,
                ]);
            });

            $this->toastSuccess('Producto fitosanitario actualizado correctamente.');

            return redirect()->route('viticulturist.phytosanitary-products.index');
        } catch (\Exception $e) {
            \Log::error('Error al actualizar producto fitosanitario', [
                'error' => $e->getMessage(),
                'product_id' => $this->product->id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->toastError('Error al actualizar el producto fitosanitario. Por favor, intenta de nuevo.');
            return;
        }
    }

    public function render()
    {
        return view('livewire.viticulturist.phytosanitary-products.edit')
            ->layout('layouts.app');
    }
}


