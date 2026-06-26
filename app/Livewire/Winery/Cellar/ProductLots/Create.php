<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Winery\AbstractCreate;
use App\Models\GrapeVariety;
use App\Models\Wine;
use App\Services\ProductLotService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $grapeVarieties
 * @property-read float $grapeTotal
 */
class Create extends AbstractCreate
{
    // ── Básico ──────────────────────────────────────────────────────
    public string $wine_id = '';

    public string $name = '';

    public string $vintage = '';

    public string $wine_type = 'tinto';

    public string $aging_type = '';

    public string $agingtime = '';

    public string $alcohol = '';

    public string $sku = '';

    // ── Stock y precio ───────────────────────────────────────────────
    public string $quantity = '';

    public string $unit = 'botellas';

    public string $available_quantity = '';

    public string $price_per_unit = '';

    public string $cost_price = '';

    // ── Formato / comercial ──────────────────────────────────────────
    public string $ean = '';

    public string $bottle_format = '';

    public string $units_per_case = '';

    // ── Variedades de uva ────────────────────────────────────────────
    public array $grapes = [];

    // ── Certificaciones ──────────────────────────────────────────────
    public bool $sulfites = false;

    public bool $ecological = false;

    public bool $is_vegan = false;

    public bool $is_biodynamic = false;

    // ── Descripción ──────────────────────────────────────────────────
    public string $description = '';

    public string $pairing = '';

    public string $tasting_notes = '';

    public string $consumption_recommendation = '';

    public string $recommended_temperature_min = '';

    public string $recommended_temperature_max = '';

    public string $tags = '';

    // ── Analíticos ───────────────────────────────────────────────────
    public string $residual_sugar = '';

    public string $total_acidity = '';

    public string $volatile_acidity = '';

    public string $ph = '';

    // ── Viñedo ───────────────────────────────────────────────────────
    public string $vine_age = '';

    public string $altitude = '';

    public string $soil_type = '';

    // ── Elaboración ──────────────────────────────────────────────────
    public string $winemaker = '';

    public string $harvest_method = '';

    public string $fermentation_vessel = '';

    public string $oak_type = '';

    public string $oak_months = '';

    // ── Marketing ────────────────────────────────────────────────────
    public string $awards_notes = '';

    public string $production_quantity = '';

    public string $bottling_date = '';

    public string $release_date = '';

    // ── Notas ────────────────────────────────────────────────────────
    public string $notes = '';

    public function mount(): void
    {
        $this->grapes = [['grape_variety_id' => '', 'percentage' => '']];
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())->active()->orderBy('name')->get();
    }

    #[Computed]
    public function grapeVarieties()
    {
        return GrapeVariety::active()->orderBy('name')->get();
    }

    #[Computed]
    public function grapeTotal(): float
    {
        return round(collect($this->grapes)->sum(fn ($g) => (float) ($g['percentage'] ?? 0)), 2);
    }

    public function addGrape(): void
    {
        $this->grapes[] = ['grape_variety_id' => '', 'percentage' => ''];
    }

    public function removeGrape(int $index): void
    {
        if (count($this->grapes) <= 1) {
            return;
        }
        array_splice($this->grapes, $index, 1);
        $this->grapes = array_values($this->grapes);
    }

    public function updatedQuantity(): void
    {
        if ($this->available_quantity === '') {
            $this->available_quantity = $this->quantity;
        }
    }

    protected function performCreate(): void
    {
        if ((float) $this->available_quantity > (float) $this->quantity) {
            throw ValidationException::withMessages([
                'available_quantity' => __('La cantidad disponible no puede superar la cantidad total.'),
            ]);
        }

        $validGrapes = collect($this->grapes)->filter(fn ($g) => ! empty($g['grape_variety_id']));

        if ($validGrapes->isNotEmpty() && $this->grapeTotal > 100.01) {
            throw ValidationException::withMessages([
                'grapes' => __('El total de variedades no puede superar el 100%.'),
            ]);
        }

        app(ProductLotService::class)->createLot(
            $this->ownerId(),
            [
                'wine_id' => $this->wine_id ?: null,
                'name' => $this->name,
                'vintage' => $this->vintage ?: null,
                'wine_type' => $this->wine_type,
                'aging_type' => $this->aging_type ?: null,
                'agingtime' => $this->agingtime ?: null,
                'alcohol' => $this->alcohol ?: null,
                'sku' => $this->sku ?: null,
                'quantity' => $this->quantity,
                'unit' => $this->unit,
                'available_quantity' => $this->available_quantity,
                'price_per_unit' => $this->price_per_unit ?: 0,
                'cost_price' => $this->cost_price ?: null,
                'ean' => $this->ean ?: null,
                'bottle_format' => $this->bottle_format ?: null,
                'units_per_case' => $this->units_per_case ?: null,
                'sulfites' => $this->sulfites,
                'ecological' => $this->ecological,
                'is_vegan' => $this->is_vegan,
                'is_biodynamic' => $this->is_biodynamic,
                'description' => $this->description ?: null,
                'pairing' => $this->pairing ?: null,
                'tasting_notes' => $this->tasting_notes ?: null,
                'consumption_recommendation' => $this->consumption_recommendation ?: null,
                'recommended_temperature_min' => $this->recommended_temperature_min ?: null,
                'recommended_temperature_max' => $this->recommended_temperature_max ?: null,
                'tags' => $this->tags ?: null,
                'residual_sugar' => $this->residual_sugar ?: null,
                'total_acidity' => $this->total_acidity ?: null,
                'volatile_acidity' => $this->volatile_acidity ?: null,
                'ph' => $this->ph ?: null,
                'vine_age' => $this->vine_age ?: null,
                'altitude' => $this->altitude ?: null,
                'soil_type' => $this->soil_type ?: null,
                'winemaker' => $this->winemaker ?: null,
                'harvest_method' => $this->harvest_method ?: null,
                'fermentation_vessel' => $this->fermentation_vessel ?: null,
                'oak_type' => $this->oak_type ?: null,
                'oak_months' => $this->oak_months ?: null,
                'awards_notes' => $this->awards_notes ?: null,
                'production_quantity' => $this->production_quantity ?: null,
                'bottling_date' => $this->bottling_date ?: null,
                'release_date' => $this->release_date ?: null,
                'notes' => $this->notes ?: null,
            ],
            $validGrapes->values()->all(),
        );
    }

    protected function successMessage(): string
    {
        return __('Producto creado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.product-lots.index';
    }

    protected function viewData(): array
    {
        return [
            'wines' => $this->wines,
            'grapeVarieties' => $this->grapeVarieties,
            'grapeTotal' => $this->grapeTotal,
        ];
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['nullable', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'name' => 'required|string|max:255',
            'vintage' => 'nullable|integer|min:1900|max:'.(now()->year + 1),
            'wine_type' => 'required|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type' => 'nullable|in:joven,crianza,reserva,gran_reserva,autor',
            'agingtime' => 'nullable|integer|min:0|max:999',
            'alcohol' => 'nullable|numeric|min:0|max:100',
            'sku' => 'nullable|string|max:100',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|in:litros,botellas,cajas',
            'available_quantity' => 'required|numeric|min:0',
            'price_per_unit' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'ean' => 'nullable|string|max:14',
            'bottle_format' => 'nullable|string|max:20',
            'units_per_case' => 'nullable|integer|min:1|max:255',
            'grapes' => 'array',
            'grapes.*.grape_variety_id' => 'nullable|integer|exists:grape_varieties,id',
            'grapes.*.percentage' => 'nullable|numeric|min:0|max:100',
            'sulfites' => 'boolean',
            'ecological' => 'boolean',
            'is_vegan' => 'boolean',
            'is_biodynamic' => 'boolean',
            'description' => 'nullable|string',
            'pairing' => 'nullable|string',
            'tasting_notes' => 'nullable|string',
            'consumption_recommendation' => 'nullable|string',
            'recommended_temperature_min' => 'nullable|numeric|min:-20|max:100',
            'recommended_temperature_max' => 'nullable|numeric|min:-20|max:100|gte:recommended_temperature_min',
            'tags' => 'nullable|string|max:500',
            'residual_sugar' => 'nullable|numeric|min:0',
            'total_acidity' => 'nullable|numeric|min:0',
            'volatile_acidity' => 'nullable|numeric|min:0',
            'ph' => 'nullable|numeric|min:0|max:14',
            'vine_age' => 'nullable|integer|min:0|max:999',
            'altitude' => 'nullable|integer|min:0|max:9999',
            'soil_type' => 'nullable|string|max:255',
            'winemaker' => 'nullable|string|max:255',
            'harvest_method' => 'nullable|in:manual,mechanic,mixed',
            'fermentation_vessel' => 'nullable|string|max:255',
            'oak_type' => 'nullable|string|max:255',
            'oak_months' => 'nullable|integer|min:0|max:999',
            'awards_notes' => 'nullable|string',
            'production_quantity' => 'nullable|integer|min:0',
            'bottling_date' => 'nullable|date',
            'release_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
