<?php

namespace App\Livewire\Viticulturist\FieldApplicators;

use App\Models\FieldApplicator;
use App\Livewire\Concerns\WithToastNotifications;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Index extends Component
{
    use WithToastNotifications;

    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingId = null;

    // Campos del formulario
    public $name = '';
    public $ropo_number = '';
    public $ropo_category = 'qualified';
    public $ropo_expiry_date = '';
    public $is_advisor = false;
    public $advisor_license = '';
    public $phone = '';
    public $email = '';

    protected function rules(): array
    {
        return [
            'name'             => 'required|string|max:100',
            'ropo_number'      => 'required|string|max:30',
            'ropo_category'    => 'required|in:basic,qualified,fumigator,pilot',
            'ropo_expiry_date' => 'nullable|date',
            'is_advisor'       => 'boolean',
            'advisor_license'  => 'nullable|string|max:50|required_if:is_advisor,true',
            'phone'            => 'nullable|string|max:20',
            'email'            => 'nullable|email|max:100',
        ];
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function openEdit(int $id)
    {
        $applicator = FieldApplicator::forViticulturist(Auth::id())->findOrFail($id);
        $this->editingId          = $id;
        $this->name               = $applicator->name;
        $this->ropo_number        = $applicator->ropo_number;
        $this->ropo_category      = $applicator->ropo_category;
        $this->ropo_expiry_date   = $applicator->ropo_expiry_date?->format('Y-m-d') ?? '';
        $this->is_advisor         = $applicator->is_advisor;
        $this->advisor_license    = $applicator->advisor_license ?? '';
        $this->phone              = $applicator->phone ?? '';
        $this->email              = $applicator->email ?? '';
        $this->showEditModal      = true;
    }

    public function save()
    {
        $this->validate();

        FieldApplicator::create([
            'viticulturist_id' => Auth::id(),
            'name'             => $this->name,
            'ropo_number'      => $this->ropo_number,
            'ropo_category'    => $this->ropo_category,
            'ropo_expiry_date' => $this->ropo_expiry_date ?: null,
            'is_advisor'       => $this->is_advisor,
            'advisor_license'  => $this->advisor_license ?: null,
            'phone'            => $this->phone ?: null,
            'email'            => $this->email ?: null,
        ]);

        $this->showCreateModal = false;
        $this->resetForm();
        $this->toastSuccess('Aplicador registrado correctamente.');
    }

    public function update()
    {
        $this->validate();

        $applicator = FieldApplicator::forViticulturist(Auth::id())->findOrFail($this->editingId);
        $applicator->update([
            'name'             => $this->name,
            'ropo_number'      => $this->ropo_number,
            'ropo_category'    => $this->ropo_category,
            'ropo_expiry_date' => $this->ropo_expiry_date ?: null,
            'is_advisor'       => $this->is_advisor,
            'advisor_license'  => $this->advisor_license ?: null,
            'phone'            => $this->phone ?: null,
            'email'            => $this->email ?: null,
        ]);

        $this->showEditModal = false;
        $this->resetForm();
        $this->toastSuccess('Aplicador actualizado correctamente.');
    }

    public function deactivate(int $id)
    {
        $applicator = FieldApplicator::forViticulturist(Auth::id())->findOrFail($id);
        $applicator->update(['active' => false]);
        $this->toastSuccess('Aplicador dado de baja.');
    }

    protected function resetForm()
    {
        $this->editingId        = null;
        $this->name             = '';
        $this->ropo_number      = '';
        $this->ropo_category    = 'qualified';
        $this->ropo_expiry_date = '';
        $this->is_advisor       = false;
        $this->advisor_license  = '';
        $this->phone            = '';
        $this->email            = '';
        $this->resetValidation();
    }

    public function render()
    {
        $applicators = FieldApplicator::forViticulturist(Auth::id())
            ->active()
            ->orderBy('name')
            ->get();

        return view('livewire.viticulturist.field-applicators.index', [
            'applicators' => $applicators,
            'categories'  => FieldApplicator::CATEGORIES,
        ])->layout('layouts.app');
    }
}
