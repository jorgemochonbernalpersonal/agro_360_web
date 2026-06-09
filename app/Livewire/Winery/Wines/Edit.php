<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Oenologist;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Wine $wine;

    public string $name = '';

    public string $vintage = '';

    public string $wine_type = '';

    public string $aging_type = '';

    public string $category = '';

    public string $status = '';

    public string $variety = '';

    public string $volume_liters = '';

    public string $internal_code = '';

    public bool $is_must = false;

    public bool $is_organic = false;

    public string $oenologist_id = '';

    public string $notes = '';

    public function mount(Wine $wine): void
    {
        $this->authorize('update', $wine);
        $this->wine = $wine;
        $this->name = $wine->name;
        $this->vintage = (string) ($wine->vintage ?? '');
        $this->wine_type = $wine->wine_type;
        $this->aging_type = $wine->aging_type ?? '';
        $this->category = $wine->category ?? '';
        $this->status = $wine->status;
        $this->variety = $wine->variety ?? '';
        $this->volume_liters = $wine->volume_liters !== null ? (string) $wine->volume_liters : '';
        $this->internal_code = $wine->internal_code ?? '';
        $this->is_must = (bool) $wine->is_must;
        $this->is_organic = (bool) $wine->is_organic;
        $this->oenologist_id = $wine->oenologist_id ? (string) $wine->oenologist_id : '';
        $this->notes = $wine->notes ?? '';
    }

    public function save(): void
    {
        $this->validate();

        $this->wine->update([
            'oenologist_id' => $this->oenologist_id ?: null,
            'name' => $this->name,
            'vintage' => $this->vintage ?: null,
            'wine_type' => $this->wine_type,
            'aging_type' => $this->aging_type ?: null,
            'category' => $this->category ?: null,
            'status' => $this->status,
            'variety' => $this->variety ?: null,
            'volume_liters' => $this->volume_liters ?: null,
            'internal_code' => $this->internal_code ?: null,
            'is_must' => $this->is_must,
            'is_organic' => $this->is_organic,
            'notes' => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Vino actualizado correctamente.'));
        $this->roleRedirect('wines.index');
    }

    public function render()
    {
        $oenologists = Oenologist::where('user_id', Auth::id())->active()->orderBy('name')->get();

        return view('livewire.winery.wines.edit', [
            'types' => Wine::wineTypeOptions(),
            'statuses' => Wine::statusOptions(),
            'agingTypes' => Wine::agingTypeOptions(),
            'categories' => Wine::categoryOptions(),
            'oenologists' => $oenologists,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'vintage' => ['nullable', 'integer', 'min:1900', 'max:'.(date('Y') + 1)],
            'wine_type' => ['required', 'in:'.implode(',', array_keys(Wine::WINE_TYPES))],
            'aging_type' => ['nullable', 'in:'.implode(',', array_keys(Wine::AGING_TYPES))],
            'category' => ['nullable', 'in:'.implode(',', array_keys(Wine::CATEGORIES))],
            'status' => ['required', 'in:'.implode(',', array_keys(Wine::STATUSES))],
            'variety' => ['nullable', 'string', 'max:300'],
            'volume_liters' => ['nullable', 'numeric', 'min:0'],
            'internal_code' => ['nullable', 'string', 'max:100'],
            'is_must' => ['boolean'],
            'is_organic' => ['boolean'],
            'oenologist_id' => ['nullable', Rule::exists('oenologists', 'id')->where('user_id', Auth::id())],
            'notes' => ['nullable', 'string'],
        ];
    }
}
