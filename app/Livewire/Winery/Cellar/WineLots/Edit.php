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

    // ── Información básica ──────────────────────────────────────────
    public string $name               = '';
    public string $vintage            = '';
    public string $wine_type          = 'tinto';
    public string $aging_type         = '';
    public string $agingtime          = '';
    public string $alcohol            = '';
    public string $quantity           = '';
    public string $unit               = 'botellas';
    public string $available_quantity = '';
    public string $price_per_unit     = '';
    public string $cost_price         = '';
    public string $notes              = '';

    // ── Analíticos ──────────────────────────────────────────────────
    public string $residual_sugar   = '';
    public string $total_acidity    = '';
    public string $volatile_acidity = '';
    public string $ph               = '';

    // ── Formato / comercial ─────────────────────────────────────────
    public string $ean            = '';
    public string $bottle_format  = '';
    public string $units_per_case = '';

    // ── Winemaking ──────────────────────────────────────────────────
    public string $winemaker           = '';
    public string $harvest_method      = '';
    public string $fermentation_vessel = '';
    public string $oak_type            = '';
    public string $oak_months          = '';

    // ── Viñedo ──────────────────────────────────────────────────────
    public string $vine_age  = '';
    public string $altitude  = '';
    public string $soil_type = '';

    // ── Certificaciones ─────────────────────────────────────────────
    public bool $is_vegan      = false;
    public bool $is_biodynamic = false;

    // ── Marketing ───────────────────────────────────────────────────
    public string $awards_notes        = '';
    public string $production_quantity = '';
    public string $bottling_date       = '';
    public string $release_date        = '';

    public function mount(WineLot $lot): void
    {
        abort_if($lot->user_id !== Auth::id(), 403);

        $this->lot               = $lot;
        $this->name               = $lot->name;
        $this->vintage            = (string) ($lot->vintage ?? '');
        $this->wine_type          = $lot->wine_type;
        $this->aging_type         = $lot->aging_type ?? '';
        $this->agingtime          = (string) ($lot->agingtime ?? '');
        $this->alcohol            = (string) ($lot->alcohol ?? '');
        $this->quantity           = (string) $lot->quantity;
        $this->unit               = $lot->unit;
        $this->available_quantity = (string) $lot->available_quantity;
        $this->price_per_unit     = (string) $lot->price_per_unit;
        $this->cost_price         = (string) ($lot->cost_price ?? '');
        $this->notes              = $lot->notes ?? '';
        // Analíticos
        $this->residual_sugar   = (string) ($lot->residual_sugar ?? '');
        $this->total_acidity    = (string) ($lot->total_acidity ?? '');
        $this->volatile_acidity = (string) ($lot->volatile_acidity ?? '');
        $this->ph               = (string) ($lot->ph ?? '');
        // Formato / comercial
        $this->ean            = $lot->ean ?? '';
        $this->bottle_format  = $lot->bottle_format ?? '';
        $this->units_per_case = (string) ($lot->units_per_case ?? '');
        // Winemaking
        $this->winemaker           = $lot->winemaker ?? '';
        $this->harvest_method      = $lot->harvest_method ?? '';
        $this->fermentation_vessel = $lot->fermentation_vessel ?? '';
        $this->oak_type            = $lot->oak_type ?? '';
        $this->oak_months          = (string) ($lot->oak_months ?? '');
        // Viñedo
        $this->vine_age  = (string) ($lot->vine_age ?? '');
        $this->altitude  = (string) ($lot->altitude ?? '');
        $this->soil_type = $lot->soil_type ?? '';
        // Certificaciones
        $this->is_vegan      = (bool) $lot->is_vegan;
        $this->is_biodynamic = (bool) $lot->is_biodynamic;
        // Marketing
        $this->awards_notes        = $lot->awards_notes ?? '';
        $this->production_quantity = (string) ($lot->production_quantity ?? '');
        $this->bottling_date       = $lot->bottling_date?->format('Y-m-d') ?? '';
        $this->release_date        = $lot->release_date?->format('Y-m-d') ?? '';
    }

    protected function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'vintage'            => 'nullable|integer|min:1900|max:' . (now()->year + 1),
            'wine_type'          => 'required|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type'         => 'nullable|in:joven,crianza,reserva,gran_reserva,autor',
            'agingtime'          => 'nullable|integer|min:0|max:999',
            'alcohol'            => 'nullable|numeric|min:0|max:100',
            'quantity'           => 'required|numeric|min:0',
            'unit'               => 'required|in:litros,botellas,cajas',
            'available_quantity' => 'required|numeric|min:0',
            'price_per_unit'     => 'nullable|numeric|min:0',
            'cost_price'         => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string',
            'residual_sugar'     => 'nullable|numeric|min:0',
            'total_acidity'      => 'nullable|numeric|min:0',
            'volatile_acidity'   => 'nullable|numeric|min:0',
            'ph'                 => 'nullable|numeric|min:0|max:14',
            'ean'                => 'nullable|string|max:14',
            'bottle_format'      => 'nullable|string|max:20',
            'units_per_case'     => 'nullable|integer|min:1|max:255',
            'winemaker'          => 'nullable|string|max:255',
            'harvest_method'     => 'nullable|in:manual,mechanic,mixed',
            'fermentation_vessel'=> 'nullable|string|max:255',
            'oak_type'           => 'nullable|string|max:255',
            'oak_months'         => 'nullable|integer|min:0|max:999',
            'vine_age'           => 'nullable|integer|min:0|max:999',
            'altitude'           => 'nullable|integer|min:0|max:9999',
            'soil_type'          => 'nullable|string|max:255',
            'is_vegan'           => 'boolean',
            'is_biodynamic'      => 'boolean',
            'awards_notes'       => 'nullable|string',
            'production_quantity'=> 'nullable|integer|min:0',
            'bottling_date'      => 'nullable|date',
            'release_date'       => 'nullable|date',
        ];
    }

    public function update()
    {
        $data = $this->validate();

        $newQuantity = (float) $data['quantity'];
        $committed   = (float) $this->lot->reserved_quantity + (float) $this->lot->sold_quantity;

        if ($newQuantity < $committed) {
            $this->addError('quantity', "La cantidad total no puede ser menor que lo ya comprometido o vendido ({$committed} {$this->lot->unit}).");
            return;
        }

        if ((float) $data['available_quantity'] > $newQuantity - (float) $this->lot->reserved_quantity - (float) $this->lot->sold_quantity) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad libre (total − reservado − vendido).');
            return;
        }

        $this->lot->update([
            'name'                => $data['name'],
            'vintage'             => $data['vintage'] ?: null,
            'wine_type'           => $data['wine_type'],
            'aging_type'          => $data['aging_type'] ?: null,
            'agingtime'           => $data['agingtime'] ?: null,
            'alcohol'             => $data['alcohol'] ?: null,
            'quantity'            => $data['quantity'],
            'unit'                => $data['unit'],
            'available_quantity'  => $data['available_quantity'],
            'price_per_unit'      => $data['price_per_unit'] ?: 0,
            'cost_price'          => $data['cost_price'] ?: null,
            'notes'               => $data['notes'] ?: null,
            'residual_sugar'      => $data['residual_sugar'] ?: null,
            'total_acidity'       => $data['total_acidity'] ?: null,
            'volatile_acidity'    => $data['volatile_acidity'] ?: null,
            'ph'                  => $data['ph'] ?: null,
            'ean'                 => $data['ean'] ?: null,
            'bottle_format'       => $data['bottle_format'] ?: null,
            'units_per_case'      => $data['units_per_case'] ?: null,
            'winemaker'           => $data['winemaker'] ?: null,
            'harvest_method'      => $data['harvest_method'] ?: null,
            'fermentation_vessel' => $data['fermentation_vessel'] ?: null,
            'oak_type'            => $data['oak_type'] ?: null,
            'oak_months'          => $data['oak_months'] ?: null,
            'vine_age'            => $data['vine_age'] ?: null,
            'altitude'            => $data['altitude'] ?: null,
            'soil_type'           => $data['soil_type'] ?: null,
            'is_vegan'            => $this->is_vegan,
            'is_biodynamic'       => $this->is_biodynamic,
            'awards_notes'        => $data['awards_notes'] ?: null,
            'production_quantity' => $data['production_quantity'] ?: null,
            'bottling_date'       => $data['bottling_date'] ?: null,
            'release_date'        => $data['release_date'] ?: null,
        ]);

        $this->toastSuccess('Producto actualizado correctamente.');
        return $this->redirect(route('winery.wine-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.wine-lots.edit')->layout('layouts.app');
    }
}
