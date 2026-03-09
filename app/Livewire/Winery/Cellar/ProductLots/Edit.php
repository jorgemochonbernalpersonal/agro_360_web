<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\GrapeVariety;
use App\Models\ProductLot;
use App\Models\Tax;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public ProductLot $lot;

    // ── Básico ──────────────────────────────────────────────────────
    public string $name       = '';
    public string $vintage    = '';
    public string $wine_type  = 'tinto';
    public string $aging_type = '';
    public string $agingtime  = '';
    public string $alcohol    = '';
    public string $sku        = '';

    // ── Stock y precio ───────────────────────────────────────────────
    public string $quantity           = '';
    public string $unit               = 'botellas';
    public string $available_quantity = '';
    public string $price_per_unit     = '';
    public string $cost_price         = '';

    // ── Formato / comercial ──────────────────────────────────────────
    public string $ean            = '';
    public string $bottle_format  = '';
    public string $units_per_case = '';

    // ── Variedades de uva ────────────────────────────────────────────
    public array $grapes = [];

    // ── Impuestos ────────────────────────────────────────────────────
    public array $selectedTaxIds = [];

    // ── Certificaciones ──────────────────────────────────────────────
    public bool $sulfites      = false;
    public bool $ecological    = false;
    public bool $is_vegan      = false;
    public bool $is_biodynamic = false;

    // ── Descripción ──────────────────────────────────────────────────
    public string $description                = '';
    public string $pairing                    = '';
    public string $tasting_notes              = '';
    public string $consumption_recommendation = '';
    public string $recommended_temperature_min = '';
    public string $recommended_temperature_max = '';
    public string $tags                       = '';

    // ── Analíticos ──────────────────────────────────────────────────
    public string $residual_sugar   = '';
    public string $total_acidity    = '';
    public string $volatile_acidity = '';
    public string $ph               = '';

    // ── Viñedo ──────────────────────────────────────────────────────
    public string $vine_age  = '';
    public string $altitude  = '';
    public string $soil_type = '';

    // ── Elaboración ──────────────────────────────────────────────────
    public string $winemaker           = '';
    public string $harvest_method      = '';
    public string $fermentation_vessel = '';
    public string $oak_type            = '';
    public string $oak_months          = '';

    // ── Marketing ────────────────────────────────────────────────────
    public string $awards_notes        = '';
    public string $production_quantity = '';
    public string $bottling_date       = '';
    public string $release_date        = '';

    // ── Notas ────────────────────────────────────────────────────────
    public string $notes = '';

    public function mount(ProductLot $lot): void
    {
        abort_if($lot->user_id !== Auth::id(), 403);

        $this->lot = $lot;

        // Básico
        $this->name       = $lot->name;
        $this->vintage    = (string) ($lot->vintage ?? '');
        $this->wine_type  = $lot->wine_type;
        $this->aging_type = $lot->aging_type ?? '';
        $this->agingtime  = (string) ($lot->agingtime ?? '');
        $this->alcohol    = (string) ($lot->alcohol ?? '');
        $this->sku        = $lot->sku ?? '';

        // Stock y precio
        $this->quantity           = (string) $lot->quantity;
        $this->unit               = $lot->unit;
        $this->available_quantity = (string) $lot->available_quantity;
        $this->price_per_unit     = (string) $lot->price_per_unit;
        $this->cost_price         = (string) ($lot->cost_price ?? '');

        // Formato / comercial
        $this->ean            = $lot->ean ?? '';
        $this->bottle_format  = $lot->bottle_format ?? '';
        $this->units_per_case = (string) ($lot->units_per_case ?? '');

        // Certificaciones
        $this->sulfites      = (bool) $lot->sulfites;
        $this->ecological    = (bool) $lot->ecological;
        $this->is_vegan      = (bool) $lot->is_vegan;
        $this->is_biodynamic = (bool) $lot->is_biodynamic;

        // Descripción
        $this->description                = $lot->description ?? '';
        $this->pairing                    = $lot->pairing ?? '';
        $this->tasting_notes              = $lot->tasting_notes ?? '';
        $this->consumption_recommendation = $lot->consumption_recommendation ?? '';
        $this->recommended_temperature_min = (string) ($lot->recommended_temperature_min ?? '');
        $this->recommended_temperature_max = (string) ($lot->recommended_temperature_max ?? '');
        $this->tags                       = $lot->tags ?? '';

        // Analíticos
        $this->residual_sugar   = (string) ($lot->residual_sugar ?? '');
        $this->total_acidity    = (string) ($lot->total_acidity ?? '');
        $this->volatile_acidity = (string) ($lot->volatile_acidity ?? '');
        $this->ph               = (string) ($lot->ph ?? '');

        // Viñedo
        $this->vine_age  = (string) ($lot->vine_age ?? '');
        $this->altitude  = (string) ($lot->altitude ?? '');
        $this->soil_type = $lot->soil_type ?? '';

        // Elaboración
        $this->winemaker           = $lot->winemaker ?? '';
        $this->harvest_method      = $lot->harvest_method ?? '';
        $this->fermentation_vessel = $lot->fermentation_vessel ?? '';
        $this->oak_type            = $lot->oak_type ?? '';
        $this->oak_months          = (string) ($lot->oak_months ?? '');

        // Marketing
        $this->awards_notes        = $lot->awards_notes ?? '';
        $this->production_quantity = (string) ($lot->production_quantity ?? '');
        $this->bottling_date       = $lot->bottling_date?->format('Y-m-d') ?? '';
        $this->release_date        = $lot->release_date?->format('Y-m-d') ?? '';

        // Notas
        $this->notes = $lot->notes ?? '';

        // Cargar variedades de uva existentes
        $existingGrapes = $lot->grapeVarieties()->get();
        $this->grapes = $existingGrapes->isEmpty()
            ? [['grape_variety_id' => '', 'percentage' => '']]
            : $existingGrapes->map(fn($g) => [
                'grape_variety_id' => (string) $g->id,
                'percentage'       => (string) $g->pivot->percentage,
            ])->toArray();

        // Cargar impuestos asignados
        $this->selectedTaxIds = $lot->taxes()->pluck('taxes.id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    #[Computed]
    public function grapeVarieties()
    {
        return GrapeVariety::active()->orderBy('name')->get();
    }

    #[Computed]
    public function userTaxes()
    {
        return Tax::join('user_taxes', 'taxes.id', '=', 'user_taxes.tax_id')
            ->where('user_taxes.user_id', Auth::id())
            ->where('taxes.active', true)
            ->select('taxes.*', 'user_taxes.is_default as user_is_default')
            ->orderBy('user_taxes.order')
            ->get();
    }

    #[Computed]
    public function grapeTotal(): float
    {
        return round(collect($this->grapes)->sum(fn($g) => (float) ($g['percentage'] ?? 0)), 2);
    }

    public function addGrape(): void
    {
        $this->grapes[] = ['grape_variety_id' => '', 'percentage' => ''];
    }

    public function removeGrape(int $index): void
    {
        if (count($this->grapes) <= 1) return;
        array_splice($this->grapes, $index, 1);
        $this->grapes = array_values($this->grapes);
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
            'sku'                => 'nullable|string|max:100',
            'quantity'           => 'required|numeric|min:0',
            'unit'               => 'required|in:litros,botellas,cajas',
            'available_quantity' => 'required|numeric|min:0',
            'price_per_unit'     => 'nullable|numeric|min:0',
            'cost_price'         => 'nullable|numeric|min:0',
            'ean'                => 'nullable|string|max:14',
            'bottle_format'      => 'nullable|string|max:20',
            'units_per_case'     => 'nullable|integer|min:1|max:255',
            'grapes'             => 'array',
            'grapes.*.grape_variety_id' => 'nullable|integer|exists:grape_varieties,id',
            'grapes.*.percentage'       => 'nullable|numeric|min:0|max:100',
            'selectedTaxIds'     => 'array',
            'selectedTaxIds.*'   => 'integer|exists:taxes,id',
            'sulfites'           => 'boolean',
            'ecological'         => 'boolean',
            'is_vegan'           => 'boolean',
            'is_biodynamic'      => 'boolean',
            'description'                => 'nullable|string',
            'pairing'                    => 'nullable|string',
            'tasting_notes'              => 'nullable|string',
            'consumption_recommendation' => 'nullable|string',
            'recommended_temperature_min' => 'nullable|numeric|min:-20|max:100',
            'recommended_temperature_max' => 'nullable|numeric|min:-20|max:100|gte:recommended_temperature_min',
            'tags'               => 'nullable|string|max:500',
            'residual_sugar'     => 'nullable|numeric|min:0',
            'total_acidity'      => 'nullable|numeric|min:0',
            'volatile_acidity'   => 'nullable|numeric|min:0',
            'ph'                 => 'nullable|numeric|min:0|max:14',
            'vine_age'           => 'nullable|integer|min:0|max:999',
            'altitude'           => 'nullable|integer|min:0|max:9999',
            'soil_type'          => 'nullable|string|max:255',
            'winemaker'          => 'nullable|string|max:255',
            'harvest_method'     => 'nullable|in:manual,mechanic,mixed',
            'fermentation_vessel'=> 'nullable|string|max:255',
            'oak_type'           => 'nullable|string|max:255',
            'oak_months'         => 'nullable|integer|min:0|max:999',
            'awards_notes'       => 'nullable|string',
            'production_quantity'=> 'nullable|integer|min:0',
            'bottling_date'      => 'nullable|date',
            'release_date'       => 'nullable|date',
            'notes'              => 'nullable|string',
        ];
    }

    public function update(): void
    {
        $data = $this->validate();

        $newQuantity = (float) $data['quantity'];
        $committed   = (float) $this->lot->reserved_quantity + (float) $this->lot->sold_quantity;

        if ($newQuantity < $committed) {
            $this->addError('quantity', "La cantidad total no puede ser menor que lo ya comprometido ({$committed} {$this->lot->unit}).");
            return;
        }

        if ((float) $data['available_quantity'] > $newQuantity - $committed) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad libre (total − reservado − vendido).');
            return;
        }

        $validGrapes = collect($this->grapes)->filter(fn($g) => !empty($g['grape_variety_id']));

        if ($validGrapes->isNotEmpty() && $this->grapeTotal > 100.01) {
            $this->addError('grapes', 'El total de variedades no puede superar el 100%.');
            return;
        }

        DB::transaction(function () use ($data, $validGrapes) {
            $this->lot->update([
                'name'                => $data['name'],
                'vintage'             => $data['vintage'] ?: null,
                'wine_type'           => $data['wine_type'],
                'aging_type'          => $data['aging_type'] ?: null,
                'agingtime'           => $data['agingtime'] ?: null,
                'alcohol'             => $data['alcohol'] ?: null,
                'sku'                 => $data['sku'] ?: null,
                'quantity'            => $data['quantity'],
                'unit'                => $data['unit'],
                'available_quantity'  => $data['available_quantity'],
                'price_per_unit'      => $data['price_per_unit'] ?: 0,
                'cost_price'          => $data['cost_price'] ?: null,
                'ean'                 => $data['ean'] ?: null,
                'bottle_format'       => $data['bottle_format'] ?: null,
                'units_per_case'      => $data['units_per_case'] ?: null,
                'sulfites'            => $this->sulfites,
                'ecological'          => $this->ecological,
                'is_vegan'            => $this->is_vegan,
                'is_biodynamic'       => $this->is_biodynamic,
                'description'                => $data['description'] ?: null,
                'pairing'                    => $data['pairing'] ?: null,
                'tasting_notes'              => $data['tasting_notes'] ?: null,
                'consumption_recommendation' => $data['consumption_recommendation'] ?: null,
                'recommended_temperature_min' => $data['recommended_temperature_min'] ?: null,
                'recommended_temperature_max' => $data['recommended_temperature_max'] ?: null,
                'tags'               => $data['tags'] ?: null,
                'residual_sugar'     => $data['residual_sugar'] ?: null,
                'total_acidity'      => $data['total_acidity'] ?: null,
                'volatile_acidity'   => $data['volatile_acidity'] ?: null,
                'ph'                 => $data['ph'] ?: null,
                'vine_age'           => $data['vine_age'] ?: null,
                'altitude'           => $data['altitude'] ?: null,
                'soil_type'          => $data['soil_type'] ?: null,
                'winemaker'          => $data['winemaker'] ?: null,
                'harvest_method'     => $data['harvest_method'] ?: null,
                'fermentation_vessel'=> $data['fermentation_vessel'] ?: null,
                'oak_type'           => $data['oak_type'] ?: null,
                'oak_months'         => $data['oak_months'] ?: null,
                'awards_notes'       => $data['awards_notes'] ?: null,
                'production_quantity'=> $data['production_quantity'] ?: null,
                'bottling_date'      => $data['bottling_date'] ?: null,
                'release_date'       => $data['release_date'] ?: null,
                'notes'              => $data['notes'] ?: null,
            ]);

            // Sincronizar variedades de uva
            $syncGrapes = $validGrapes->mapWithKeys(fn($g) => [
                $g['grape_variety_id'] => ['percentage' => (float) ($g['percentage'] ?? 0)],
            ])->toArray();
            $this->lot->grapeVarieties()->sync($syncGrapes);

            // Sincronizar impuestos
            $this->lot->taxes()->sync($this->selectedTaxIds);
        });

        $this->toastSuccess('Producto actualizado correctamente.');
        $this->redirect(route('winery.product-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.product-lots.edit')->layout('layouts.app');
    }
}
