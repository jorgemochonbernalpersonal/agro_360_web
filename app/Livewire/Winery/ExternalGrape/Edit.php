<?php

namespace App\Livewire\Winery\ExternalGrape;

use App\Models\Container;
use App\Models\ExternalGrape;
use App\Models\GrapeVariety;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public ExternalGrape $grape;

    public string $supplier_name     = '';
    public string $grape_type        = 'grapes';
    public string $grape_variety_id  = '';
    public string $color             = '';
    public string $protection_level  = '';
    public string $geographic_origin = '';
    public string $vintage_year      = '';
    public string $alcohol_pct       = '';
    public string $total_weight_kg   = '';
    public string $entry_date        = '';
    public string $harvest_date      = '';
    public string $expiration_date   = '';
    public string $container_id      = '';
    public string $notes             = '';
    public string $status            = 'available';

    public function mount(ExternalGrape $grape): void
    {
        abort_if($grape->user_id !== Auth::id(), 403);
        $this->grape = $grape;

        $this->supplier_name     = $grape->supplier_name;
        $this->grape_type        = $grape->grape_type;
        $this->grape_variety_id  = (string) ($grape->grape_variety_id ?? '');
        $this->color             = $grape->color ?? '';
        $this->protection_level  = $grape->protection_level ?? '';
        $this->geographic_origin = $grape->geographic_origin ?? '';
        $this->vintage_year      = (string) ($grape->vintage_year ?? '');
        $this->alcohol_pct       = (string) ($grape->alcohol_pct ?? '');
        $this->total_weight_kg   = (string) $grape->total_weight_kg;
        $this->entry_date        = $grape->entry_date->toDateString();
        $this->harvest_date      = $grape->harvest_date?->toDateString() ?? '';
        $this->expiration_date   = $grape->expiration_date?->toDateString() ?? '';
        $this->container_id      = (string) ($grape->container_id ?? '');
        $this->notes             = $grape->notes ?? '';
        $this->status            = $grape->status;
    }

    protected function rules(): array
    {
        return [
            'supplier_name'     => 'required|string|max:200',
            'grape_type'        => 'required|in:grapes,must,bulk_wine',
            'grape_variety_id'  => 'nullable|exists:grape_varieties,id',
            'color'             => 'nullable|in:white,red,rose,other',
            'protection_level'  => 'nullable|string|max:100',
            'geographic_origin' => 'nullable|string|max:200',
            'vintage_year'      => 'nullable|integer|min:1900|max:2100',
            'alcohol_pct'       => 'nullable|numeric|min:0|max:100',
            'total_weight_kg'   => 'required|numeric|min:0.001',
            'entry_date'        => 'required|date',
            'harvest_date'      => 'nullable|date',
            'expiration_date'   => 'nullable|date',
            'container_id'      => ['nullable', Rule::exists('containers', 'id')->where('user_id', Auth::id())],
            'notes'             => 'nullable|string',
            'status'            => 'required|in:available,used,archived',
        ];
    }

    public function update(): void
    {
        $this->validate();

        $this->grape->update([
            'supplier_name'    => $this->supplier_name,
            'grape_type'       => $this->grape_type,
            'grape_variety_id' => $this->grape_variety_id ?: null,
            'color'            => $this->color ?: null,
            'protection_level' => $this->protection_level ?: null,
            'geographic_origin'=> $this->geographic_origin ?: null,
            'vintage_year'     => $this->vintage_year ?: null,
            'alcohol_pct'      => $this->alcohol_pct ?: null,
            'total_weight_kg'  => $this->total_weight_kg,
            'entry_date'       => $this->entry_date,
            'harvest_date'     => $this->harvest_date ?: null,
            'expiration_date'  => $this->expiration_date ?: null,
            'container_id'     => $this->container_id ?: null,
            'notes'            => $this->notes ?: null,
            'status'           => $this->status,
        ]);

        $this->toastSuccess(__('Partida actualizada correctamente.'));
        $this->roleRedirect('external-grape.index');
    }

    public function render()
    {
        return view('livewire.winery.external-grape.edit', [
            'varieties'  => GrapeVariety::orderBy('name')->get(['id', 'name']),
            'containers' => Container::where('user_id', Auth::id())->active()->orderBy('name')->get(['id', 'name']),
            'types'      => ExternalGrape::typeOptions(),
            'colors'     => ExternalGrape::colorOptions(),
        ])->layout('layouts.app');
    }
}
