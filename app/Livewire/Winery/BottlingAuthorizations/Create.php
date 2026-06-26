<?php

namespace App\Livewire\Winery\BottlingAuthorizations;

use App\Livewire\Winery\AbstractCreate;
use App\Models\BottlingAuthorization;
use App\Models\Wine;
use Illuminate\Support\Facades\Auth;

class Create extends AbstractCreate
{
    public string $authorization_number = '';

    public string $authorization_type = 'standard';

    public string $wine_id = '';

    public string $authorized_volume_liters = '';

    public string $valid_from = '';

    public string $valid_until = '';

    public string $issuing_authority = '';

    public string $status = 'active';

    public string $conditions = '';

    public string $notes = '';

    protected function performCreate(): void
    {
        BottlingAuthorization::create([
            'user_id' => $this->ownerId(),
            'authorization_number' => $this->authorization_number,
            'authorization_type' => $this->authorization_type,
            'wine_id' => $this->wine_id ?: null,
            'authorized_volume_liters' => $this->authorized_volume_liters !== '' ? $this->authorized_volume_liters : null,
            'valid_from' => $this->valid_from ?: null,
            'valid_until' => $this->valid_until ?: null,
            'issuing_authority' => $this->issuing_authority ?: null,
            'status' => $this->status,
            'conditions' => $this->conditions ?: null,
            'notes' => $this->notes ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return "Autorización «{$this->authorization_number}» creada correctamente.";
    }

    protected function indexRoute(): string
    {
        return 'winery.bottling-authorizations.index';
    }

    protected function viewData(): array
    {
        return [
            'types' => BottlingAuthorization::authorizationTypeOptions(),
            'statuses' => BottlingAuthorization::statusOptions(),
            'wines' => Wine::where('user_id', Auth::id())->orderBy('name')->get(),
        ];
    }

    protected function rules(): array
    {
        return [
            'authorization_number' => ['required', 'string', 'max:100'],
            'authorization_type' => ['required', 'in:'.implode(',', array_keys(BottlingAuthorization::AUTHORIZATION_TYPES))],
            'wine_id' => $this->ownedWineRule(false),
            'authorized_volume_liters' => ['nullable', 'numeric', 'min:0'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date'],
            'issuing_authority' => ['nullable', 'string', 'max:200'],
            'status' => ['required', 'in:'.implode(',', array_keys(BottlingAuthorization::STATUSES))],
            'conditions' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
