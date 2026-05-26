<?php

namespace App\Livewire\Winery\Wines\Process;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithOwnershipRules, WithToastNotifications, WithRoleAwareRedirect;

    public Wine $wine;

    public string $process_type            = 'fermentation';
    public string $container_id            = '';
    public string $start_date              = '';
    public string $end_date                = '';
    public string $quantity                = '';
    public string $unit_of_measurement_id  = '';
    public string $observations            = '';

    // Contenedores adicionales para procesos multi-contenedor (blending, trasiego)
    public array $extraContainers = [];

    public function mount(Wine $wine): void
    {
        abort_if($wine->user_id !== Auth::id(), 403);
        $this->wine       = $wine;
        $this->start_date = now()->toDateString();
    }

    public function addExtraContainer(): void
    {
        $this->extraContainers[] = ['container_id' => '', 'quantity' => ''];
    }

    public function removeExtraContainer(int $index): void
    {
        array_splice($this->extraContainers, $index, 1);
    }

    protected function rules(): array
    {
        return [
            'process_type'           => ['required', 'in:' . implode(',', array_keys(WineProcessDetail::PROCESS_TYPES))],
            'container_id'           => $this->ownedContainerRule(false),
            'start_date'             => ['required', 'date'],
            'end_date'               => ['nullable', 'date', 'after_or_equal:start_date'],
            'quantity'               => ['nullable', 'numeric', 'min:0'],
            'unit_of_measurement_id' => ['nullable', 'exists:units_of_measurement,id'],
            'observations'           => ['nullable', 'string'],
            'extraContainers.*.container_id' => $this->ownedContainerRule(false),
            'extraContainers.*.quantity'     => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $detail = WineProcessDetail::create([
            'wine_id'                => $this->wine->id,
            'container_id'           => $this->container_id ?: null,
            'process_type'           => $this->process_type,
            'start_date'             => $this->start_date,
            'end_date'               => $this->end_date ?: null,
            'quantity'               => $this->quantity ?: null,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'observations'           => $this->observations ?: null,
            'created_by'             => Auth::id(),
        ]);

        // Contenedores adicionales
        foreach ($this->extraContainers as $row) {
            if (empty($row['container_id'])) continue;
            $detail->containers()->attach($row['container_id'], [
                'quantity'               => $row['quantity'] ?: null,
                'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            ]);
        }

        $this->toastSuccess(__('Operación de vinificación registrada correctamente.'));
        $this->roleRedirect('wines.edit', $this->wine);
    }

    public function render()
    {
        $containers = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->orderBy('name')
            ->get();

        return view('livewire.winery.wines.process.create', [
            'processTypes' => WineProcessDetail::PROCESS_TYPES,
            'containers'   => $containers,
            'units'        => UnitOfMeasurement::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
