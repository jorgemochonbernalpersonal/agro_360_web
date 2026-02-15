<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Models\Plot;
use App\Models\PhytosanitaryProduct;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class ActivityFilters extends Component
{
    public $selectedCampaign = null;
    public $selectedPlot = null;
    public $activityType = null;
    public $search = '';
    public $dateFrom = null;
    public $dateTo = null;
    public $productFilter = null;

    protected $queryString = [
        'selectedPlot' => ['except' => ''],
        'activityType' => ['except' => ''],
        'search' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'productFilter' => ['except' => ''],
    ];

    public function mount($selectedCampaign = null)
    {
        $this->selectedCampaign = $selectedCampaign;
    }

    public function render()
    {
        $user = Auth::user();
        
        // Obtener parcelas activas
        $plots = Plot::forUser($user)
            ->where('active', true)
            ->select(['id', 'name', 'area'])
            ->orderBy('name')
            ->get();

        // Obtener productos fitosanitarios si es necesario
        $products = $this->activityType === 'phytosanitary'
            ? PhytosanitaryProduct::select(['id', 'name'])
                ->where('active', true)
                ->orderBy('name')
                ->get()
            : collect();

        return view('livewire.viticulturist.digital-notebook.activity-filters', [
            'plots' => $plots,
            'products' => $products,
        ]);
    }

    public function clearFilters()
    {
        $this->selectedPlot = null;
        $this->activityType = null;
        $this->search = '';
        $this->dateFrom = null;
        $this->dateTo = null;
        $this->productFilter = null;
        
        $this->dispatch('filters-cleared');
    }

    public function updatedActivityType()
    {
        $this->productFilter = null;
        $this->dispatch('activity-type-changed', $this->activityType);
    }

    public function getFilters(): array
    {
        return [
            'campaign_id' => $this->selectedCampaign,
            'plot_id' => $this->selectedPlot,
            'activity_type' => $this->activityType,
            'search' => $this->search,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'product_filter' => $this->productFilter,
        ];
    }
}
