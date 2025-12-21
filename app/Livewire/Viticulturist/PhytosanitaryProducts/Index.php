<?php

namespace App\Livewire\Viticulturist\PhytosanitaryProducts;

use App\Models\PhytosanitaryProduct;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => ''],
    ];

    public function mount(): void
    {
        // Solo viticultores pueden gestionar el catálogo en esta vista
        if (! Auth::user()->isViticulturist()) {
            abort(403, 'No tienes permiso para ver productos fitosanitarios.');
        }
    }

    public function render()
    {
        $query = PhytosanitaryProduct::query()
            ->orderBy('name');

        if ($this->search) {
            $search = '%' . strtolower($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(active_ingredient) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(registration_number) LIKE ?', [$search])
                  ->orWhereRaw('LOWER(manufacturer) LIKE ?', [$search]);
            });
        }

        if ($this->typeFilter) {
            $query->whereRaw('LOWER(type) = ?', [strtolower($this->typeFilter)]);
        }

        $products = $query->paginate(10);

        // Tipos únicos para el filtro
        $types = PhytosanitaryProduct::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type');

        return view('livewire.viticulturist.phytosanitary-products.index', [
            'products' => $products,
            'types' => $types,
        ])->layout('layouts.app', [
            'title' => 'Productos Fitosanitarios - Agro365',
            'description' => 'Catálogo completo de productos fitosanitarios. Gestiona tratamientos, plazos de seguridad y cumplimiento normativo para tu viñedo.',
        ]);
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->typeFilter = '';
        $this->resetPage();
    }
}


