<?php

namespace App\Livewire\Winery\Bottling;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
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
use Livewire\Attributes\Computed;
use Livewire\Component;

class Edit extends Component
{
    use WithOwnershipRules, WithRoleAwareRedirect, WithToastNotifications;

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
        abort_if($bottling->user_id !== Auth::id(), 403);

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

    public function save(): void
    {
        $data = $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($data['wine_id']);

        // Validate container has enough wine (add back old quantity if same container)
        if (! empty($this->container_id)) {
            $container = Container::where('user_id', Auth::id())->find($this->container_id);
            if ($container) {
                $oldQty = (float) ($this->oldBottlingData['container_id'] == $this->container_id ? $this->oldBottlingData['quantity_liters'] : 0);
                $available = $container->wine_volume_liters + $oldQty;
                if ($available < (float) $this->quantity_liters) {
                    $this->addError('quantity_liters', __('El depósito solo tiene :volume L disponibles para embotellar.', ['volume' => number_format($available, 1)]));

                    return;
                }
            }
        }

        DB::transaction(function () use ($data) {
            $this->bottling->update([
                'wine_id' => $data['wine_id'],
                'wine_process_detail_id' => $data['wine_process_detail_id'] ?: null,
                'product_lot_id' => $data['product_lot_id'] ?: null,
                'oenologist_id' => $data['oenologist_id'] ?: null,
                'bottling_date' => $data['bottling_date'],
                'bottle_format' => $data['bottle_format'],
                'quantity_bottles' => $data['quantity_bottles'],
                'quantity_liters' => $data['quantity_liters'],
                'lot_number' => $data['lot_number'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);

            $this->bottling->refresh();
            app(WineContainerStockService::class)->updateBottling($this->bottling, $this->oldBottlingData);

            $this->bottling->supplies()->delete();

            foreach ($this->supplies as $row) {
                if (empty($row['supply_name']) && empty($row['winery_supply_id'])) {
                    continue;
                }

                $this->bottling->supplies()->create([
                    'winery_supply_id' => $row['winery_supply_id'] ?: null,
                    'supply_name' => $row['supply_name'] ?: '',
                    'quantity' => $row['quantity'] ?: 0,
                    'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                    'notes' => $row['notes'] ?: null,
                ]);
            }
        });

        $this->toastSuccess(__('Embotellado actualizado correctamente.'));
        $this->roleRedirect('bottling.index');
    }

    public function render()
    {
        return view('livewire.winery.bottling.edit', [
            'bottleFormats' => WineBottling::bottleFormatOptions(),
            'wines' => $this->wines,
            'originContainer' => $this->originContainer,
            'oenologists' => $this->oenologists,
            'winerySupplies' => $this->winerySupplies,
            'units' => $this->units,
            'bottlingProcessDetails' => $this->bottlingProcessDetails,
        ])->layout('layouts.app');
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
}
