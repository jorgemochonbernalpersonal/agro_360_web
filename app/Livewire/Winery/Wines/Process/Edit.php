<?php

namespace App\Livewire\Winery\Wines\Process;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineProcessDetail;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public Wine              $wine;
    public WineProcessDetail $process;

    public string $process_type           = '';
    public string $container_id           = '';
    public string $start_date             = '';
    public string $end_date               = '';
    public string $quantity               = '';
    public string $unit_of_measurement_id = '';
    public string $observations           = '';

    public array $extraContainers = [];

    public function mount(Wine $wine, WineProcessDetail $process): void
    {
        abort_if($wine->user_id !== Auth::id(), 403);
        abort_if($process->wine_id !== $wine->id, 404);

        $this->wine    = $wine;
        $this->process = $process;

        $this->process_type           = $process->process_type;
        $this->container_id           = (string) ($process->container_id ?? '');
        $this->start_date             = $process->start_date?->toDateString() ?? '';
        $this->end_date               = $process->end_date?->toDateString() ?? '';
        $this->quantity               = (string) ($process->quantity ?? '');
        $this->unit_of_measurement_id = (string) ($process->unit_of_measurement_id ?? '');
        $this->observations           = $process->observations ?? '';

        $this->extraContainers = $process->containers
            ->map(fn($c) => [
                'container_id' => (string) $c->id,
                'quantity'     => (string) ($c->pivot->quantity ?? ''),
            ])
            ->toArray();
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

        $this->process->update([
            'process_type'           => $this->process_type,
            'container_id'           => $this->container_id ?: null,
            'start_date'             => $this->start_date,
            'end_date'               => $this->end_date ?: null,
            'quantity'               => $this->quantity ?: null,
            'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            'observations'           => $this->observations ?: null,
        ]);

        // Sync extra containers
        $sync = [];
        foreach ($this->extraContainers as $row) {
            if (empty($row['container_id'])) continue;
            $sync[$row['container_id']] = [
                'quantity'               => $row['quantity'] ?: null,
                'unit_of_measurement_id' => $this->unit_of_measurement_id ?: null,
            ];
        }
        $this->process->containers()->sync($sync);

        $this->toastSuccess(__('Operación de vinificación actualizada correctamente.'));
        $this->redirect(roleRoute('wines.edit', $this->wine), navigate: true);
    }

    public function render()
    {
        $containers = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->orderBy('name')
            ->get();

        return view('livewire.winery.wines.process.edit', [
            'processTypes' => WineProcessDetail::PROCESS_TYPES,
            'containers'   => $containers,
            'units'        => UnitOfMeasurement::orderBy('name')->get(),
        ])->layout('layouts.app');
    }
}
