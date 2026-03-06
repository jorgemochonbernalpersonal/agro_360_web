<?php

namespace App\Livewire\Winery\Cellar\WineLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\WineLot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public WineLot $lot;

    public string $name               = '';
    public string $vintage            = '';
    public string $wine_type          = 'tinto';
    public string $quantity           = '';
    public string $unit               = 'litros';
    public string $available_quantity = '';
    public string $price_per_unit     = '';
    public string $notes              = '';

    public function mount(WineLot $lot): void
    {
        abort_if($lot->user_id !== Auth::id(), 403);

        $this->lot               = $lot;
        $this->name               = $lot->name;
        $this->vintage            = (string) ($lot->vintage ?? '');
        $this->wine_type          = $lot->wine_type;
        $this->quantity           = (string) $lot->quantity;
        $this->unit               = $lot->unit;
        $this->available_quantity = (string) $lot->available_quantity;
        $this->price_per_unit     = (string) $lot->price_per_unit;
        $this->notes              = $lot->notes ?? '';
    }

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

    public function update()
    {
        $data = $this->validate();

        $newQuantity  = (float) $data['quantity'];
        $committed    = (float) $this->lot->reserved_quantity + (float) $this->lot->sold_quantity;

        if ($newQuantity < $committed) {
            $this->addError('quantity', "La cantidad total no puede ser menor que lo ya comprometido o vendido ({$committed} {$this->lot->unit}).");
            return;
        }

        if ((float) $data['available_quantity'] > $newQuantity - (float) $this->lot->reserved_quantity - (float) $this->lot->sold_quantity) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad libre (total − reservado − vendido).');
            return;
        }

        $this->lot->update([
            'name'               => $data['name'],
            'vintage'            => $data['vintage'] ?: null,
            'wine_type'          => $data['wine_type'],
            'quantity'           => $data['quantity'],
            'unit'               => $data['unit'],
            'available_quantity' => $data['available_quantity'],
            'price_per_unit'     => $data['price_per_unit'] ?: 0,
            'notes'              => $data['notes'] ?: null,
        ]);

        $this->toastSuccess('Lote de vino actualizado.');
        return $this->redirect(route('winery.wine-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.wine-lots.edit')->layout('layouts.app');
    }
}
