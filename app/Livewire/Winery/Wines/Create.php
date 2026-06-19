<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Winery\AbstractCreate;
use App\Models\Oenologist;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $vintage = '';

    public string $wine_type = 'red';

    public string $aging_type = '';

    public string $category = '';

    public string $status = 'in_progress';

    public string $variety = '';

    public string $volume_liters = '';

    public string $internal_code = '';

    public bool $is_must = false;

    public bool $is_organic = false;

    public string $oenologist_id = '';

    public string $notes = '';

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

    protected function performCreate(): void
    {
        Wine::create([
            'user_id' => $this->ownerId(),
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
    }

    protected function successMessage(): string
    {
        return __('Vino «:name» creado correctamente.', ['name' => $this->name]);
    }

    protected function indexRoute(): string
    {
        return 'winery.wines.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => Wine::wineTypeOptions(),
            'statuses' => Wine::statusOptions(),
            'agingTypes' => Wine::agingTypeOptions(),
            'categories' => Wine::categoryOptions(),
            'oenologists' => Oenologist::where('user_id', Auth::id())->active()->orderBy('name')->get(),
        ];
    }
}
