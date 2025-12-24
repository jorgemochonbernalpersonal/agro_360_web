<?php

namespace App\Livewire\Viticulturist\PestManagement;

use App\Models\Pest;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all'; // all, pest, disease
    public $showOnlyRisk = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingShowOnlyRisk()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Pest::query()->active();

        // Filtro de búsqueda
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('scientific_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por tipo
        if ($this->typeFilter !== 'all') {
            $query->byType($this->typeFilter);
        }

        // Filtro por riesgo actual
        if ($this->showOnlyRisk) {
            $query->inRiskPeriod();
        }

        $pests = $query->orderBy('type')->orderBy('name')->paginate(12);

        // Obtener plagas en riesgo para alertas
        $pestsInRisk = Pest::active()->inRiskPeriod()->get();

        return view('livewire.viticulturist.pest-management.index', [
            'pests' => $pests,
            'pestsInRisk' => $pestsInRisk,
        ])->layout('layouts.app');
    }
}
