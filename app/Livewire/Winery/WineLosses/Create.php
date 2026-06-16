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
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $wine_id = '';

    public string $container_id = '';

    public string $loss_type = 'evaporation';

    public string $loss_authorization = 'processing';

    public string $regulatory_reference = '';

    public string $quantity = '';

    public string $unit_of_measurement_id = '';

    public string $loss_date = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->loss_date = now()->toDateString();
    }

    #[Computed]
    public function containers()
    {
        if (! $this->wine_id) {
            return Container::where('user_id', Auth::id())
                ->where('archived', false)
                ->orderBy('name')
                ->get();
        }

        // Primero los contenedores que tienen este vino; luego el resto
        $withWine = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->whereHas('currentStates', fn ($q) => $q->where('wine_id', $this->wine_id))
            ->orderBy('name')
            ->get();

        $others = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->whereDoesntHave('currentStates', fn ($q) => $q->where('wine_id', $this->wine_id))
            ->orderBy('name')
            ->get();

        return $withWine->merge($others);
    }

    public function updatedWineId(): void
    {
        $this->container_id = '';
        unset($this->containers);
    }

    public function save(): void
    {
        $this->validate();

        Wine::where('user_id', Auth::id())->findOrFail($this->wine_id);

        // Validate container has enough wine stock
        if ($this->container_id) {
            $container = Container::where('user_id', Auth::id())->find($this->container_id);
            if ($container && $container->wine_volume_liters < (float) $this->quantity) {
                $this->addError('quantity', __('El depósito solo tiene :volume L disponibles para embotellar.', ['volume' => number_format((float) $container->wine_volume_liters, 1)]));

                return;
            }
        }

        $loss = WineLoss::create([
            'wine_id' => $this->wine_id,
            'container_id' => $this->container_id ?: null,
            'loss_type' => $this->loss_type,
            'loss_authorization' => $this->loss_authorization ?: null,
            'regulatory_reference' => $this->regulatory_reference ?: null,
            'quantity' => $this->quantity,
            'unit_of_measurement_id' => $this->unit_of_measurement_id,
            'loss_date' => $this->loss_date,
            'notes' => $this->notes ?: null,
            'created_by' => Auth::id(),
        ]);

        app(WineContainerStockService::class)->recordLoss($loss);

        $this->toastSuccess(__('Merma registrada correctamente.'));
        $this->redirect(roleRoute('wine-losses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.wine-losses.create', [
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
            'containers' => $this->containers,
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
