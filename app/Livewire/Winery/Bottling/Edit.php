<?php

namespace App\Livewire\Winery\Bottling;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Winery\AbstractEdit;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineProcessDetail;
use App\Models\WinerySupply;
use App\Services\WineBottlingService;
use Illuminate\Support\Facades\Auth;
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
 * @property-read mixed $originContainer
 */
class Edit extends AbstractEdit
{
    use WithOwnershipRules;

    public WineBottling $bottling;

    public array $oldBottlingData = [];

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

    public array $supplies = [];

    public function mount(WineBottling $bottling): void
    {
        $this->authorize('update', $bottling);

        $this->bottling = $bottling;

        $this->oldBottlingData = [
            'wine_id' => $bottling->wine_id,
            'container_id' => $bottling->container_id,
            'quantity_liters' => $bottling->quantity_liters,
        ];

        $this->wine_id = (string) $bottling->wine_id;
        $this->container_id = (string) ($bottling->container_id ?? '');
        $this->bottling_date = $bottling->bottling_date->toDateString();
        $this->bottle_format = $bottling->bottle_format;
        $this->quantity_bottles = (string) $bottling->quantity_bottles;
        $this->quantity_liters = (string) $bottling->quantity_liters;
        $this->lot_number = $bottling->lot_number ?? '';
        $this->oenologist_id = (string) ($bottling->oenologist_id ?? '');
        $this->wine_process_detail_id = (string) ($bottling->wine_process_detail_id ?? '');
        $this->product_lot_id = (string) ($bottling->product_lot_id ?? '');
        $this->notes = $bottling->notes ?? '';

        $this->supplies = $bottling->supplies->map(fn ($s) => [
            'winery_supply_id' => (string) ($s->winery_supply_id ?? ''),
            'supply_name' => $s->supply_name,
            'quantity' => (string) $s->quantity,
            'unit_of_measurement_id' => (string) ($s->unit_of_measurement_id ?? ''),
            'notes' => $s->notes ?? '',
        ])->toArray();
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
    public function originContainer()
    {
        if (! $this->container_id) {
            return null;
        }

        return Container::find($this->container_id);
    }

    public function updatedWineId(): void
    {
        $this->wine_process_detail_id = '';
        unset($this->bottlingProcessDetails);
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
            'supplies.*.supply_name' => ['nullable', 'string', 'max:255'],
            'supplies.*.quantity' => ['nullable', 'numeric', 'min:0'],
            'supplies.*.unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'supplies.*.notes' => ['nullable', 'string'],
        ];
    }

    protected function performUpdate(): void
    {
        Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        // Validate container has enough wine (add back old quantity if same container)
        if ($this->container_id !== '') {
            $container = Container::where('user_id', Auth::id())->find($this->container_id);
            if ($container) {
                $oldQty = (float) ($this->oldBottlingData['container_id'] == $this->container_id ? $this->oldBottlingData['quantity_liters'] : 0);
                $available = $container->wine_volume_liters + $oldQty;
                if ($available < (float) $this->quantity_liters) {
                    throw ValidationException::withMessages([
                        'quantity_liters' => __('El depósito solo tiene :volume L disponibles para embotellar.', ['volume' => number_format($available, 1)]),
                    ]);
                }
            }
        }

        app(WineBottlingService::class)->updateBottling(
            $this->bottling,
            [
                'wine_id' => $this->wine_id,
                'wine_process_detail_id' => $this->wine_process_detail_id ?: null,
                'product_lot_id' => $this->product_lot_id ?: null,
                'oenologist_id' => $this->oenologist_id ?: null,
                'bottling_date' => $this->bottling_date,
                'bottle_format' => $this->bottle_format,
                'quantity_bottles' => $this->quantity_bottles,
                'quantity_liters' => $this->quantity_liters,
                'lot_number' => $this->lot_number ?: null,
                'notes' => $this->notes ?: null,
            ],
            $this->supplies,
            $this->oldBottlingData,
        );
    }

    protected function successMessage(): string
    {
        return __('Embotellado actualizado correctamente.');
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
            'originContainer' => $this->originContainer,
            'oenologists' => $this->oenologists,
            'winerySupplies' => $this->winerySupplies,
            'units' => $this->units,
            'bottlingProcessDetails' => $this->bottlingProcessDetails,
        ];
    }
}
