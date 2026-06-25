<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Winery\AbstractEdit;
use App\Models\GrapeVariety;
use App\Models\ProductLot;
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
class Edit extends AbstractEdit
{
    public ProductLot $lot;

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

    // ── Analíticos ──────────────────────────────────────────────────
    public string $residual_sugar = '';

    public string $total_acidity = '';

    public string $volatile_acidity = '';

    public string $ph = '';

    // ── Viñedo ──────────────────────────────────────────────────────
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

    public function mount(ProductLot $lot): void
    {
        $this->authorize('update', $lot);

        $this->lot = $lot;

        // Básico
        $this->wine_id = (string) ($lot->wine_id ?? '');
        $this->name = $lot->name;
        $this->vintage = (string) ($lot->vintage ?? '');
        $this->wine_type = $lot->wine_type;
        $this->aging_type = $lot->aging_type ?? '';
        $this->agingtime = (string) ($lot->agingtime ?? '');
        $this->alcohol = (string) ($lot->alcohol ?? '');
        $this->sku = $lot->sku ?? '';

        // Stock y precio
        $this->quantity = (string) $lot->quantity;
        $this->unit = $lot->unit;
        $this->available_quantity = (string) $lot->available_quantity;
        $this->price_per_unit = (string) $lot->price_per_unit;
        $this->cost_price = (string) ($lot->cost_price ?? '');

        // Formato / comercial
        $this->ean = $lot->ean ?? '';
        $this->bottle_format = $lot->bottle_format ?? '';
        $this->units_per_case = (string) ($lot->units_per_case ?? '');

        // Certificaciones
        $this->sulfites = (bool) $lot->sulfites;
        $this->ecological = (bool) $lot->ecological;
        $this->is_vegan = (bool) $lot->is_vegan;
        $this->is_biodynamic = (bool) $lot->is_biodynamic;

        // Descripción
        $this->description = $lot->description ?? '';
        $this->pairing = $lot->pairing ?? '';
        $this->tasting_notes = $lot->tasting_notes ?? '';
        $this->consumption_recommendation = $lot->consumption_recommendation ?? '';
        $this->recommended_temperature_min = (string) ($lot->recommended_temperature_min ?? '');
        $this->recommended_temperature_max = (string) ($lot->recommended_temperature_max ?? '');
        $this->tags = $lot->tags ?? '';

        // Analíticos
        $this->residual_sugar = (string) ($lot->residual_sugar ?? '');
        $this->total_acidity = (string) ($lot->total_acidity ?? '');
        $this->volatile_acidity = (string) ($lot->volatile_acidity ?? '');
        $this->ph = (string) ($lot->ph ?? '');

        // Viñedo
        $this->vine_age = (string) ($lot->vine_age ?? '');
        $this->altitude = (string) ($lot->altitude ?? '');
        $this->soil_type = $lot->soil_type ?? '';

        // Elaboración
        $this->winemaker = $lot->winemaker ?? '';
        $this->harvest_method = $lot->harvest_method ?? '';
        $this->fermentation_vessel = $lot->fermentation_vessel ?? '';
        $this->oak_type = $lot->oak_type ?? '';
        $this->oak_months = (string) ($lot->oak_months ?? '');

        // Marketing
        $this->awards_notes = $lot->awards_notes ?? '';
        $this->production_quantity = (string) ($lot->production_quantity ?? '');
        $this->bottling_date = $lot->bottling_date?->format('Y-m-d') ?? '';
        $this->release_date = $lot->release_date?->format('Y-m-d') ?? '';

        // Notas
        $this->notes = $lot->notes ?? '';

        // Variedades de uva
        $existingGrapes = $lot->grapeVarieties()->get();
        $this->grapes = $existingGrapes->isEmpty()
            ? [['grape_variety_id' => '', 'percentage' => '']]
            : $existingGrapes->map(fn ($g) => [
                'grape_variety_id' => (string) $g->id,
                'percentage' => (string) $g->pivot->percentage,
            ])->toArray();
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

    protected function performUpdate(): void
    {
        $newQuantity = (float) $this->quantity;
        $committed = (float) $this->lot->reserved_quantity + (float) $this->lot->sold_quantity;

        if ($newQuantity < $committed) {
            throw ValidationException::withMessages([
                'quantity' => __('La cantidad total no puede ser menor que lo ya comprometido (:committed :unit).', [
                    'committed' => $committed,
                    'unit' => $this->lot->unit,
                ]),
            ]);
        }

        // If quantity increased, add the increment directly to available
        $increment = max(0.0, $newQuantity - (float) $this->lot->quantity);
        if ($increment > 0) {
            $this->available_quantity = (string) round((float) $this->lot->available_quantity + $increment, 3);
        }

        if ((float) $this->available_quantity > $newQuantity - $committed) {
            throw ValidationException::withMessages([
                'available_quantity' => __('La cantidad disponible no puede superar la cantidad libre (total − reservado − vendido).'),
            ]);
        }

        $validGrapes = collect($this->grapes)->filter(fn ($g) => ! empty($g['grape_variety_id']));

        if ($validGrapes->isNotEmpty() && $this->grapeTotal > 100.01) {
            throw ValidationException::withMessages([
                'grapes' => __('El total de variedades no puede superar el 100%.'),
            ]);
        }

        app(ProductLotService::class)->updateLot(
            $this->lot,
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
        return __('Producto actualizado correctamente.');
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
