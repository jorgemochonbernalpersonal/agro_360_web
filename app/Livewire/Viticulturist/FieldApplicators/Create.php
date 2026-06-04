<?php

namespace App\Livewire\Viticulturist\FieldApplicators;

use App\Livewire\Viticulturist\AbstractCreate;
use App\Models\FieldApplicator;

class Create extends AbstractCreate
{
    public string $name = '';

    public string $ropo_number = '';

    public string $ropo_category = 'qualified';

    public string $ropo_expiry_date = '';

    public bool $is_advisor = false;

    public string $advisor_license = '';

    public string $phone = '';

    public string $email = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:100',
            'ropo_number' => 'required|string|max:30',
            'ropo_category' => 'required|in:'.implode(',', array_keys(FieldApplicator::CATEGORIES)),
            'ropo_expiry_date' => 'nullable|date',
            'is_advisor' => 'boolean',
            'advisor_license' => 'nullable|string|max:50|required_if:is_advisor,true',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
        ];
    }

    protected function performCreate(): void
    {
        FieldApplicator::create([
            'viticulturist_id' => $this->viticulturistId(),
            'name' => $this->name,
            'ropo_number' => $this->ropo_number,
            'ropo_category' => $this->ropo_category,
            'ropo_expiry_date' => $this->ropo_expiry_date ?: null,
            'is_advisor' => $this->is_advisor,
            'advisor_license' => $this->advisor_license ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
        ]);
    }

    protected function successMessage(): string
    {
        return __('Aplicador registrado correctamente.');
    }

    protected function indexRoute(): string
    {
        return 'viticulturist.field-applicators.index';
    }

    protected function viewData(): array
    {
        return [
            'categories' => FieldApplicator::CATEGORIES,
        ];
    }
}
