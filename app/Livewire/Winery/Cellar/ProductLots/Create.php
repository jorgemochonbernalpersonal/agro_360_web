<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\ProductLot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    // ── Información básica ──────────────────────────────────────────
    public string $name              = '';
    public string $vintage           = '';
    public string $wine_type         = 'tinto';
    public string $aging_type        = '';
    public string $agingtime         = '';
    public string $alcohol           = '';
    public string $quantity          = '';
    public string $unit              = 'botellas';
    public string $available_quantity= '';
    public string $price_per_unit    = '';
    public string $cost_price        = '';
    public string $notes             = '';

    // ── Analíticos ──────────────────────────────────────────────────
    public string $residual_sugar   = '';
    public string $total_acidity    = '';
    public string $volatile_acidity = '';
    public string $ph               = '';

    // ── Formato / comercial ─────────────────────────────────────────
    public string $ean            = '';
    public string $bottle_format  = '75cl';
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

    protected function rules(): array
    {
        return [
            // Básicos
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
            // Analíticos
            'residual_sugar'    => 'nullable|numeric|min:0',
            'total_acidity'     => 'nullable|numeric|min:0',
            'volatile_acidity'  => 'nullable|numeric|min:0',
            'ph'                => 'nullable|numeric|min:0|max:14',
            // Formato / comercial
            'ean'            => 'nullable|string|max:14',
            'bottle_format'  => 'nullable|string|max:20',
            'units_per_case' => 'nullable|integer|min:1|max:255',
            // Winemaking
            'winemaker'           => 'nullable|string|max:255',
            'harvest_method'      => 'nullable|in:manual,mechanic,mixed',
            'fermentation_vessel' => 'nullable|string|max:255',
            'oak_type'            => 'nullable|string|max:255',
            'oak_months'          => 'nullable|integer|min:0|max:999',
            // Viñedo
            'vine_age'  => 'nullable|integer|min:0|max:999',
            'altitude'  => 'nullable|integer|min:0|max:9999',
            'soil_type' => 'nullable|string|max:255',
            // Certificaciones
            'is_vegan'      => 'boolean',
            'is_biodynamic' => 'boolean',
            // Marketing
            'awards_notes'        => 'nullable|string',
            'production_quantity' => 'nullable|integer|min:0',
            'bottling_date'       => 'nullable|date',
            'release_date'        => 'nullable|date',
        ];
    }

    public function updatedQuantity(): void
    {
        if ($this->available_quantity === '') {
            $this->available_quantity = $this->quantity;
        }
    }

    public function save()
    {
        $data = $this->validate();

        if ((float) $data['available_quantity'] > (float) $data['quantity']) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad total.');
            return;
        }

        ProductLot::create([
            'user_id'             => Auth::id(),
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
            'archived'            => false,
            // Analíticos
            'residual_sugar'    => $data['residual_sugar'] ?: null,
            'total_acidity'     => $data['total_acidity'] ?: null,
            'volatile_acidity'  => $data['volatile_acidity'] ?: null,
            'ph'                => $data['ph'] ?: null,
            // Formato / comercial
            'ean'            => $data['ean'] ?: null,
            'bottle_format'  => $data['bottle_format'] ?: null,
            'units_per_case' => $data['units_per_case'] ?: null,
            // Winemaking
            'winemaker'           => $data['winemaker'] ?: null,
            'harvest_method'      => $data['harvest_method'] ?: null,
            'fermentation_vessel' => $data['fermentation_vessel'] ?: null,
            'oak_type'            => $data['oak_type'] ?: null,
            'oak_months'          => $data['oak_months'] ?: null,
            // Viñedo
            'vine_age'  => $data['vine_age'] ?: null,
            'altitude'  => $data['altitude'] ?: null,
            'soil_type' => $data['soil_type'] ?: null,
            // Certificaciones
            'is_vegan'      => $this->is_vegan,
            'is_biodynamic' => $this->is_biodynamic,
            // Marketing
            'awards_notes'        => $data['awards_notes'] ?: null,
            'production_quantity' => $data['production_quantity'] ?: null,
            'bottling_date'       => $data['bottling_date'] ?: null,
            'release_date'        => $data['release_date'] ?: null,
        ]);

        $this->toastSuccess('Producto creado correctamente.');
        return $this->redirect(route('winery.product-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.product-lots.create')->layout('layouts.app');
    }
}
