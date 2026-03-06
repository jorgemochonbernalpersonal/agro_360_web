<?php

namespace App\Livewire\Winery\Cellar\WineLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineLot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $name              = '';
    public string $vintage           = '';
    public string $wine_type         = 'tinto';
    public string $quantity          = '';
    public string $unit              = 'litros';
    public string $available_quantity= '';
    public string $price_per_unit    = '';
    public string $notes             = '';

    protected function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'vintage'            => 'nullable|integer|min:1900|max:' . (now()->year + 1),
            'wine_type'          => 'required|in:tinto,blanco,rosado,espumoso,otro',
            'quantity'           => 'required|numeric|min:0',
            'unit'               => 'required|in:litros,botellas,cajas',
            'available_quantity' => 'required|numeric|min:0',
            'price_per_unit'     => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
        ];
    }

    public function updatedQuantity(): void
    {
        // Sync available with quantity if not yet set
        if ($this->available_quantity === '') {
            $this->available_quantity = $this->quantity;
        }
    }

    public function save()
    {
        $data = $this->validate();

        // Ensure available <= quantity
        if ((float) $data['available_quantity'] > (float) $data['quantity']) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad total.');
            return;
        }

        WineLot::create([
            'user_id'            => Auth::id(),
            'name'               => $data['name'],
            'vintage'            => $data['vintage'] ?: null,
            'wine_type'          => $data['wine_type'],
            'quantity'           => $data['quantity'],
            'unit'               => $data['unit'],
            'available_quantity' => $data['available_quantity'],
            'price_per_unit'     => $data['price_per_unit'] ?: 0,
            'notes'              => $data['notes'] ?: null,
            'archived'           => false,
        ]);

        $this->toastSuccess('Lote de vino creado correctamente.');
        return $this->redirect(route('winery.wine-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.wine-lots.create')->layout('layouts.app');
    }
}
