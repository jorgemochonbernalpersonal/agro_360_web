<?php

namespace App\Livewire\Winery\WineLosses;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineLoss;
use App\Services\WineContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public WineLoss $loss;

    public array $oldLossData = [];

    public string $wine_id = '';

    public string $container_id = '';

    public string $loss_type = 'evaporation';

    public string $loss_authorization = '';

    public string $regulatory_reference = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $loss_date = '';

    public string $notes = '';

    public function mount(WineLoss $loss): void
    {
        abort_if($loss->wine?->user_id !== Auth::id(), 403);

        $this->loss = $loss;

        $this->oldLossData = [
            'wine_id' => $loss->wine_id,
            'container_id' => $loss->container_id,
            'quantity' => $loss->quantity,
        ];

        $this->wine_id = (string) $loss->wine_id;
        $this->container_id = $loss->container_id ? (string) $loss->container_id : '';
        $this->loss_type = $loss->loss_type;
        $this->loss_authorization = $loss->loss_authorization ?? '';
        $this->regulatory_reference = $loss->regulatory_reference ?? '';
        $this->quantity = (string) $loss->quantity;
        $this->unit_of_measurement_id = (string) $loss->unit_of_measurement_id;
        $this->loss_date = $loss->loss_date instanceof \Carbon\Carbon
            ? $loss->loss_date->toDateString()
            : $loss->loss_date;
        $this->notes = $loss->notes ?? '';
    }

    public function save(): void
    {
        $this->validate();

        // Validate container has enough wine stock (add back old quantity for same container)
        if ($this->container_id) {
            $container = Container::where('user_id', Auth::id())->find($this->container_id);
            if ($container) {
                $oldQty = (float) ($this->oldLossData['container_id'] == $this->container_id ? $this->oldLossData['quantity'] : 0);
                $available = $container->wine_volume_liters + $oldQty;
                if ($available < (float) $this->quantity) {
                    $this->addError('quantity', __('El depósito solo tiene :volume L disponibles para embotellar.', ['volume' => number_format($available, 1)]));

                    return;
                }
            }
        }

        $this->loss->update([
            'wine_id' => $this->wine_id,
            'container_id' => $this->container_id ?: null,
            'loss_type' => $this->loss_type,
            'loss_authorization' => $this->loss_authorization ?: null,
            'regulatory_reference' => $this->regulatory_reference ?: null,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id,
            'loss_date' => $this->loss_date,
            'notes' => $this->notes ?: null,
        ]);

        $this->loss->refresh();
        app(WineContainerStockService::class)->updateLoss($this->loss, $this->oldLossData);

        $this->toastSuccess(__('Merma actualizada.'));
        $this->redirect(roleRoute('wine-losses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.wine-losses.edit', [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers' => Container::where('user_id', Auth::id())->where('archived', false)->orderBy('name')->get(),
            'units' => UnitOfMeasurement::orderBy('name')->get(),
            'lossTypes' => WineLoss::lossTypeOptions(),
            'lossAuths' => WineLoss::lossAuthorizationOptions(),
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'wine_id' => ['required', Rule::exists('wines', 'id')->where('user_id', Auth::id())],
            'container_id' => ['nullable', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'loss_type' => ['required', 'in:'.implode(',', array_keys(WineLoss::LOSS_TYPES))],
            'loss_authorization' => ['nullable', 'in:'.implode(',', array_keys(WineLoss::LOSS_AUTHORIZATIONS))],
            'regulatory_reference' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'unit_of_measurement_id' => ['required', 'exists:units_of_measurement,id'],
            'loss_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
