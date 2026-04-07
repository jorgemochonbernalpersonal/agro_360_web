<?php

namespace App\Livewire\Winery\CellarOperations;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\CellarOperation;
use App\Models\Container;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    public string $operation_type      = 'racking';
    public string $operation_date      = '';
    public string $source_container_id = '';
    public string $target_container_id = '';
    public string $volume_liters       = '';
    public string $responsible_person  = '';
    public string $status              = 'planned';
    public string $notes               = '';

    public function mount(): void
    {
        $this->operation_date = now()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'operation_type'      => ['required', 'in:' . implode(',', array_keys(CellarOperation::OPERATION_TYPES))],
            'operation_date'      => ['required', 'date'],
            'source_container_id' => ['nullable', 'exists:containers,id'],
            'target_container_id' => ['nullable', 'exists:containers,id'],
            'volume_liters'       => ['nullable', 'numeric', 'min:0'],
            'responsible_person'  => ['nullable', 'string', 'max:150'],
            'status'              => ['required', 'in:' . implode(',', array_keys(CellarOperation::STATUSES))],
            'notes'               => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $this->validate();

        CellarOperation::create([
            'user_id'             => Auth::id(),
            'operation_type'      => $this->operation_type,
            'operation_date'      => $this->operation_date,
            'source_container_id' => $this->source_container_id ?: null,
            'target_container_id' => $this->target_container_id ?: null,
            'volume_liters'       => $this->volume_liters !== '' ? $this->volume_liters : null,
            'responsible_person'  => $this->responsible_person ?: null,
            'status'              => $this->status,
            'notes'               => $this->notes ?: null,
        ]);

        $typeLabel = CellarOperation::OPERATION_TYPES[$this->operation_type] ?? $this->operation_type;
        $this->toastSuccess("Operación «{$typeLabel}» creada correctamente.");
        $this->redirect(roleRoute('cellar-operations.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.winery.cellar-operations.create', [
            'containers' => Container::where('user_id', Auth::id())
                ->where('archived', false)
                ->orderBy('name')
                ->get(),
            'types'    => CellarOperation::OPERATION_TYPES,
            'statuses' => CellarOperation::STATUSES,
        ])->layout('layouts.app');
    }
}
