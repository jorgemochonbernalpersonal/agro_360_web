<?php

namespace App\Livewire\Winery\Bottling;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractCreate;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineProcessDetail;
use App\Models\WinerySupply;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;

/**
 * @property-read mixed $wines
 * @property-read mixed $bottlingProcessDetails
 * @property-read mixed $oenologists
 * @property-read mixed $winerySupplies
 * @property-read mixed $units
 * @property-read mixed $containers
 */
class Create extends AbstractCreate
{
    use WithOwnershipRules;

    // ── Datos principales ─────────────────────────────────────────────────────
    public string $wine_id = '';

    public string $container_id = '';

    public string $bottling_date = '';

    public string $bottle_format = '750';

    public string $quantity_bottles = '';

    public string $quantity_liters = '';

    public string $lot_number = '';

    public string $oenologist_id = '';

    public string $wine_process_detail_id = '';

    public string $product_lot_id = '';

    public string $notes = '';

    // ── Insumos utilizados ────────────────────────────────────────────────────
    public array $supplies = [];

    public function mount(): void
    {
        $this->bottling_date = now()->toDateString();
    }

    #[Computed]
    public function wines()
    {
        return Wine::where('user_id', Auth::id())
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function bottlingProcessDetails()
    {
        if (! $this->wine_id) {
            return collect();
        }

        return WineProcessDetail::where('wine_id', $this->wine_id)
            ->where('process_type', 'bottling')
            ->orderByDesc('start_date')
            ->get();
    }

    #[Computed]
    public function oenologists()
    {
        return Oenologist::where('user_id', Auth::id())
            ->where('active', true)
            ->orderBy('surname')
            ->get();
    }

    #[Computed]
    public function winerySupplies()
    {
        return WinerySupply::where('user_id', Auth::id())
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function units()
    {
        return UnitOfMeasurement::orderBy('name')->get();
    }

    #[Computed]
    public function containers()
    {
        if (! $this->wine_id) {
            return collect();
        }

        // Contenedores que tienen este vino en su estado actual
        $fromState = Container::where('user_id', Auth::id())
            ->whereHas('currentStates', fn ($q) => $q->where('wine_id', $this->wine_id))
            ->where('archived', false)
            ->get();

        // Contenedores enlazados al proceso seleccionado (si lo hay)
        if ($this->wine_process_detail_id) {
            $fromProcess = Container::whereHas('wineProcessDetails',
                fn ($q) => $q->where('wine_process_details.id', $this->wine_process_detail_id)
            )->get();

            return $fromState->merge($fromProcess)->unique('id')->values();
        }

        return $fromState;
    }

    public function updatedWineId(): void
    {
        $this->wine_process_detail_id = '';
        $this->container_id = '';
        unset($this->bottlingProcessDetails);
        unset($this->containers);
    }

    public function updatedWineProcessDetailId(): void
    {
        $this->container_id = '';
        unset($this->containers);
    }

    public function addSupply(): void
    {
        $this->supplies[] = [
            'winery_supply_id' => '',
            'supply_name' => '',
            'quantity' => '',
            'unit_of_measurement_id' => '',
            'notes' => '',
        ];
    }

    public function removeSupply(int $index): void
    {
        array_splice($this->supplies, $index, 1);
        $this->supplies = array_values($this->supplies);
    }

    public function updatedSupplies(mixed $value, string $key): void
    {
        // Auto-fill supply_name when a winery_supply_id is selected
        if (! str_ends_with($key, '.winery_supply_id')) {
            return;
        }

        $index = (int) explode('.', $key)[0];

        if (! empty($value)) {
            $supply = WinerySupply::find($value);
            if ($supply) {
                $this->supplies[$index]['supply_name'] = $supply->name;
                $this->supplies[$index]['unit_of_measurement_id'] = (string) ($supply->unit_of_measurement_id ?? '');
            }
        }
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'container_id' => ['nullable', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'bottling_date' => ['required', 'date'],
            'bottle_format' => ['required', 'string', 'max:20'],
            'quantity_bottles' => ['required', 'integer', 'min:1'],
            'quantity_liters' => ['required', 'numeric', 'min:0.001'],
            'lot_number' => ['nullable', 'string', 'max:100'],
            'oenologist_id' => ['nullable', Rule::exists('oenologists', 'id')->where('user_id', Auth::id())],
            'wine_process_detail_id' => $this->ownedWineProcessDetailRule(false),
            'product_lot_id' => ['nullable', Rule::exists('wine_lots', 'id')->where('user_id', Auth::id())],
            'notes' => ['nullable', 'string'],
            'supplies' => ['array'],
            'supplies.*.winery_supply_id' => ['nullable', Rule::exists('winery_supplies', 'id')->where('user_id', Auth::id())],
            'supplies.*.supply_name' => ['required_unless:supplies,', 'nullable', 'string', 'max:255'],
            'supplies.*.quantity' => ['required_unless:supplies,', 'nullable', 'numeric', 'min:0'],
            'supplies.*.unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'supplies.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'wine_id.required' => __('Debes seleccionar un vino.'),
            'quantity_bottles.required' => __('Indica la cantidad de botellas.'),
            'quantity_bottles.min' => __('La cantidad mínima es 1 botella.'),
            'quantity_liters.required' => __('Indica los litros embotellados.'),
            'quantity_liters.min' => __('La cantidad de litros debe ser mayor que cero.'),
        ];
    }

    protected function performCreate(): void
    {
        // Ownership check
        $wine = Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        // Validate container has enough wine_volume_liters to bottle
        if ($this->container_id !== '') {
            $container = Container::where('user_id', Auth::id())->find($this->container_id);
            if ($container && $container->wine_volume_liters < (float) $this->quantity_liters) {
                throw ValidationException::withMessages([
                    'quantity_liters' => __('El depósito solo tiene :volume L disponibles para embotellar.', ['volume' => number_format((float) $container->wine_volume_liters, 1)]),
                ]);
            }
        }

        DB::transaction(function () use ($wine) {
            $bottling = WineBottling::create([
                'user_id' => $this->ownerId(),
                'wine_id' => $wine->id,
                'container_id' => $this->container_id ?: null,
                'wine_process_detail_id' => $this->wine_process_detail_id ?: null,
                'product_lot_id' => $this->product_lot_id ?: null,
                'oenologist_id' => $this->oenologist_id ?: null,
                'bottling_date' => $this->bottling_date,
                'bottle_format' => $this->bottle_format,
                'quantity_bottles' => $this->quantity_bottles,
                'quantity_liters' => $this->quantity_liters,
                'lot_number' => $this->lot_number ?: null,
                'notes' => $this->notes ?: null,
                'created_by' => $this->ownerId(),
            ]);

            // Decrementar wine_volume_liters en el contenedor origen
            app(WineContainerStockService::class)->recordBottling($bottling);

            foreach ($this->supplies as $row) {
                if (empty($row['supply_name']) && empty($row['winery_supply_id'])) {
                    continue;
                }

                $bottling->supplies()->create([
                    'winery_supply_id' => $row['winery_supply_id'] ?: null,
                    'supply_name' => $row['supply_name'] ?: '',
                    'quantity' => $row['quantity'] ?: 0,
                    'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                    'notes' => $row['notes'] ?: null,
                ]);
            }
        });
    }

    protected function successMessage(): string
    {
        return __('Embotellado registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.bottling.index';
    }

    protected function viewData(): array
    {
        return [
            'bottleFormats' => WineBottling::bottleFormatOptions(),
            'wines' => $this->wines,
            'containers' => $this->containers,
            'oenologists' => $this->oenologists,
            'winerySupplies' => $this->winerySupplies,
            'units' => $this->units,
            'bottlingProcessDetails' => $this->bottlingProcessDetails,
        ];
    }
}
