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

class Create extends Component
{
    use WithToastNotifications;

    // ── Básico ──────────────────────────────────────────────────────
    public string $name      = '';
    public string $vintage   = '';
    public string $wine_type = 'tinto';
    public string $aging_type = '';
    public string $alcohol   = '';
    public string $sku       = '';

    // ── Stock y precio ───────────────────────────────────────────────
    public string $quantity           = '';
    public string $unit               = 'botellas';
    public string $available_quantity = '';
    public string $price_per_unit     = '';
    public string $cost_price         = '';

    // ── Variedades de uva ────────────────────────────────────────────
    public array $grapes = [];

    // ── Impuestos ────────────────────────────────────────────────────
    public array $selectedTaxIds = [];

    // ── Certificaciones ──────────────────────────────────────────────
    public bool $sulfites     = false;
    public bool $ecological   = false;
    public bool $is_vegan     = false;
    public bool $is_biodynamic = false;

    // ── Notas ────────────────────────────────────────────────────────
    public string $notes = '';

    public function mount(): void
    {
        $this->grapes = [['grape_variety_id' => '', 'percentage' => '']];

        // Pre-seleccionar impuestos por defecto del usuario
        $this->selectedTaxIds = Tax::join('user_taxes', 'taxes.id', '=', 'user_taxes.tax_id')
            ->where('user_taxes.user_id', Auth::id())
            ->where('user_taxes.is_default', true)
            ->where('taxes.active', true)
            ->pluck('taxes.id')
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

    public function updatedQuantity(): void
    {
        if ($this->available_quantity === '') {
            $this->available_quantity = $this->quantity;
        }
    }

    protected function rules(): array
    {
        return [
            'name'               => 'required|string|max:255',
            'vintage'            => 'nullable|integer|min:1900|max:' . (now()->year + 1),
            'wine_type'          => 'required|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type'         => 'nullable|in:joven,crianza,reserva,gran_reserva,autor',
            'alcohol'            => 'nullable|numeric|min:0|max:100',
            'sku'                => 'nullable|string|max:100',
            'quantity'           => 'required|numeric|min:0',
            'unit'               => 'required|in:litros,botellas,cajas',
            'available_quantity' => 'required|numeric|min:0',
            'price_per_unit'     => 'nullable|numeric|min:0',
            'cost_price'         => 'nullable|numeric|min:0',
            'grapes'             => 'array',
            'grapes.*.grape_variety_id' => 'nullable|integer|exists:grape_varieties,id',
            'grapes.*.percentage'       => 'nullable|numeric|min:0|max:100',
            'selectedTaxIds'     => 'array',
            'selectedTaxIds.*'   => 'integer|exists:taxes,id',
            'sulfites'           => 'boolean',
            'ecological'         => 'boolean',
            'is_vegan'           => 'boolean',
            'is_biodynamic'      => 'boolean',
            'notes'              => 'nullable|string',
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ((float) $data['available_quantity'] > (float) $data['quantity']) {
            $this->addError('available_quantity', 'La cantidad disponible no puede superar la cantidad total.');
            return;
        }

        $validGrapes = collect($this->grapes)->filter(fn($g) => !empty($g['grape_variety_id']));

        if ($validGrapes->isNotEmpty() && $this->grapeTotal > 100.01) {
            $this->addError('grapes', 'El total de variedades no puede superar el 100%.');
            return;
        }

        DB::transaction(function () use ($data, $validGrapes) {
            $lot = ProductLot::create([
                'user_id'             => Auth::id(),
                'name'                => $data['name'],
                'vintage'             => $data['vintage'] ?: null,
                'wine_type'           => $data['wine_type'],
                'aging_type'          => $data['aging_type'] ?: null,
                'alcohol'             => $data['alcohol'] ?: null,
                'sku'                 => $data['sku'] ?: null,
                'quantity'            => $data['quantity'],
                'unit'                => $data['unit'],
                'available_quantity'  => $data['available_quantity'],
                'price_per_unit'      => $data['price_per_unit'] ?: 0,
                'cost_price'          => $data['cost_price'] ?: null,
                'sulfites'            => $this->sulfites,
                'ecological'          => $this->ecological,
                'is_vegan'            => $this->is_vegan,
                'is_biodynamic'       => $this->is_biodynamic,
                'notes'               => $data['notes'] ?: null,
                'archived'            => false,
            ]);

            // Variedades de uva
            $syncGrapes = $validGrapes->mapWithKeys(fn($g) => [
                $g['grape_variety_id'] => ['percentage' => (float) ($g['percentage'] ?? 0)],
            ])->toArray();
            $lot->grapeVarieties()->sync($syncGrapes);

            // Impuestos
            $lot->taxes()->sync($this->selectedTaxIds);
        });

        $this->toastSuccess('Producto creado correctamente.');
        $this->redirect(route('winery.product-lots.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar.product-lots.create')->layout('layouts.app');
    }
}
