<?php

namespace App\Livewire\Winery\CellarOperations;

use App\Livewire\Winery\AbstractEdit;
use App\Models\CellarOperation;
use App\Models\Container;
use Illuminate\Support\Facades\Auth;

class Edit extends AbstractEdit
{
    public CellarOperation $operation;

    public string $operation_type = '';

    public string $operation_date = '';

    public string $source_container_id = '';

    public string $target_container_id = '';

    public string $volume_liters = '';

    public string $responsible_person = '';

    public string $status = '';

    public string $notes = '';

    public function mount(CellarOperation $operation): void
    {
        $this->authorize('update', $operation);

        $this->operation = $operation;
        $this->operation_type = $operation->operation_type;
        $this->operation_date = $operation->operation_date->toDateString();
        $this->source_container_id = (string) ($operation->source_container_id ?? '');
        $this->target_container_id = (string) ($operation->target_container_id ?? '');
        $this->volume_liters = $operation->volume_liters !== null ? (string) $operation->volume_liters : '';
        $this->responsible_person = $operation->responsible_person ?? '';
        $this->status = $operation->status;
        $this->notes = $operation->notes ?? '';
    }

    protected function rules(): array
    {
        return [
            'operation_type' => ['required', 'in:'.implode(',', array_keys(CellarOperation::OPERATION_TYPES))],
            'operation_date' => ['required', 'date'],
            'source_container_id' => $this->ownedContainerRule(false),
            'target_container_id' => $this->ownedContainerRule(false),
            'volume_liters' => ['nullable', 'numeric', 'min:0'],
            'responsible_person' => ['nullable', 'string', 'max:150'],
            'status' => ['required', 'in:'.implode(',', array_keys(CellarOperation::STATUSES))],
            'notes' => ['nullable', 'string'],
        ];
    }

    protected function performUpdate(): void
    {
        $this->operation->update([
            'operation_type' => $this->operation_type,
            'operation_date' => $this->operation_date,
            'source_container_id' => $this->source_container_id ?: null,
            'target_container_id' => $this->target_container_id ?: null,
            'volume_liters' => $this->volume_liters !== '' ? $this->volume_liters : null,
            'responsible_person' => $this->responsible_person ?: null,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Operación actualizada correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'winery.cellar-operations.index';
    }

    protected function viewData(): array
    {
        return [
            'containers' => Container::where('user_id', Auth::id())
                ->where('archived', false)
                ->orderBy('name')
                ->get(),
            'types' => CellarOperation::operationTypeOptions(),
            'statuses' => CellarOperation::statusOptions(),
        ];
    }
}
