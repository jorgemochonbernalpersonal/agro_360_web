<?php

namespace App\Livewire\Winery\ExternalGrape;

use App\Models\Container;
use App\Models\ExternalGrape;
use App\Models\GrapeVariety;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

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

    public function mount(): void
    {
        $this->entry_date = now()->toDateString();
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
            'container_id'      => 'nullable|exists:containers,id',
            'notes'             => 'nullable|string',
            'status'            => 'required|in:available,used,archived',
        ];
    }

    public function save(): void
    {
        $this->validate();

        ExternalGrape::create([
            'user_id'          => Auth::id(),
            'supplier_name'    => $this->supplier_name,
            'grape_type'       => $this->grape_type,
            'grape_variety_id' => $this->grape_variety_id ?: null,
            'color'            => $this->color ?: null,
            'protection_level' => $this->protection_level ?: null,
            'geographic_origin'=> $this->geographic_origin ?: null,
            'vintage_year'     => $this->vintage_year ?: null,
            'alcohol_pct'      => $this->alcohol_pct ?: null,
            'total_weight_kg'  => $this->total_weight_kg,
            'used_weight_kg'   => 0,
            'entry_date'       => $this->entry_date,
            'harvest_date'     => $this->harvest_date ?: null,
            'expiration_date'  => $this->expiration_date ?: null,
            'container_id'     => $this->container_id ?: null,
            'notes'            => $this->notes ?: null,
            'status'           => $this->status,
        ]);

        $this->toastSuccess('Partida registrada correctamente.');
        $this->roleRedirect('external-grape.index');
    }

    public function render()
    {
        return view('livewire.winery.external-grape.create', [
            'varieties'  => GrapeVariety::orderBy('name')->get(['id', 'name']),
            'containers' => Container::where('user_id', Auth::id())->active()->orderBy('name')->get(['id', 'name']),
            'types'      => ExternalGrape::TYPES,
            'colors'     => ExternalGrape::COLORS,
        ])->layout('layouts.app');
    }
}
