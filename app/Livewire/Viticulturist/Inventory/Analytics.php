<?php

namespace App\Livewire\Viticulturist\Inventory;

use App\Services\InventoryAnalyticsService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class Analytics extends Component
{
    public $period = '3months'; // 1month, 3months, 6months, 12months
    
    protected $analyticsService;

    public function boot(InventoryAnalyticsService $service)
    {
        $this->analyticsService = $service;
    }

    public function mount()
    {
        if (!Auth::user()->isViticulturist()) {
            abort(403);
        }
    }

    public function updatedPeriod()
    {
        // Refrescar datos cuando cambia el periodo
    }

    public function exportInventory()
    {
        return response()->download(
            storage_path('app/exports/inventory_' . now()->format('Y-m-d') . '.xlsx')
        );
    }

    public function render()
    {
        $userId = Auth::id();

        $monthlyConsumption = $this->analyticsService->getMonthlyConsumption($userId);
        $topProducts = $this->analyticsService->getTopConsumedProducts($userId);
        $projections = $this->analyticsService->getStockProjections($userId);
        $stats = $this->analyticsService->getGeneralStats($userId);
        $slowMoving = $this->analyticsService->getSlowMovingProducts($userId);

        return view('livewire.viticulturist.inventory.analytics', [
            'monthlyConsumption' => $monthlyConsumption,
            'topProducts' => $topProducts,
            'projections' => $projections,
            'stats' => $stats,
            'slowMoving' => $slowMoving,
        ])->layout('layouts.app', [
            'title' => 'Analíticas de Inventario - Agro365',
        ]);
    }
}
