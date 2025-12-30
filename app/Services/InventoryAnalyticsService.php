<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryAnalyticsService
{
    /**
     * Obtener consumo mensual últimos 12 meses
     */
    public function getMonthlyConsumption(int $userId): array
    {
        $data = ProductStockMovement::where('user_id', $userId)
            ->where('movement_type', 'consumption')
            ->where('created_at', '>=', now()->subMonths(12))
            ->selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                SUM(ABS(quantity_change)) as total_consumed,
                COUNT(*) as movements_count
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'labels' => $data->pluck('month')->map(function($month) {
                return Carbon::parse($month . '-01')->format('M Y');
            })->toArray(),
            'consumed' => $data->pluck('total_consumed')->toArray(),
            'count' => $data->pluck('movements_count')->toArray(),
        ];
    }

    /**
     * Top 5 productos más consumidos
     */
    public function getTopConsumedProducts(int $userId, int $limit = 5): array
    {
        return ProductStockMovement::where('product_stock_movements.user_id', $userId)
            ->where('movement_type', 'consumption')
            ->where('product_stock_movements.created_at', '>=', now()->subMonths(3))
            ->join('product_stocks', 'product_stock_movements.stock_id', '=', 'product_stocks.id')
            ->join('phytosanitary_products', 'product_stocks.product_id', '=', 'phytosanitary_products.id')
            ->selectRaw('
                phytosanitary_products.name,
                phytosanitary_products.id as product_id,
                product_stocks.unit,
                SUM(ABS(product_stock_movements.quantity_change)) as total_consumed,
                COUNT(*) as times_used
            ')
            ->groupBy('phytosanitary_products.id', 'phytosanitary_products.name', 'product_stocks.unit')
            ->orderByDesc('total_consumed')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Proyección de agotamiento por producto
     */
    public function getStockProjections(int $userId): array
    {
        $stocks = ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->where('quantity', '>', 0)
            ->with('product')
            ->get();

        $projections = [];

        foreach ($stocks as $stock) {
            // Consumo promedio últimos 30 días
            $avgDailyConsumption = ProductStockMovement::where('stock_id', $stock->id)
                ->where('movement_type', 'consumption')
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('SUM(ABS(quantity_change)) / 30 as avg_daily')
                ->value('avg_daily');

            if ($avgDailyConsumption && $avgDailyConsumption > 0) {
                $daysUntilEmpty = $stock->quantity / $avgDailyConsumption;
                $estimatedDate = now()->addDays($daysUntilEmpty);

                $projections[] = [
                    'stock_id' => $stock->id,
                    'product' => $stock->product->name,
                    'current_stock' => (float) $stock->quantity,
                    'unit' => $stock->unit,
                    'avg_daily_consumption' => round($avgDailyConsumption, 3),
                    'days_until_empty' => round($daysUntilEmpty),
                    'estimated_empty_date' => $estimatedDate,
                    'status' => $this->getProjectionStatus($daysUntilEmpty),
                ];
            }
        }

        // Ordenar por días hasta agotamiento (más urgente primero)
        usort($projections, fn($a, $b) => $a['days_until_empty'] <=> $b['days_until_empty']);

        return $projections;
    }

    /**
     * Estado de la proyección
     */
    private function getProjectionStatus(float $days): string
    {
        if ($days < 7) return 'critical';
        if ($days < 30) return 'warning';
        return 'ok';
    }

    /**
     * Valor total del inventario
     */
    public function getTotalInventoryValue(int $userId): float
    {
        return (float) ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->sum(DB::raw('quantity * COALESCE(unit_price, 0)'));
    }

    /**
     * Estadísticas generales
     */
    public function getGeneralStats(int $userId): array
    {
        $totalProducts = ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->distinct('product_id')
            ->count('product_id');

        $lowStockCount = ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->where(function($q) {
                $q->whereColumn('quantity', '<', DB::raw('COALESCE(minimum_stock, 5)'));
            })
            ->count();

        $expiringCount = ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addDays(30))
            ->count();

        $totalValue = $this->getTotalInventoryValue($userId);

        return [
            'total_products' => $totalProducts,
            'low_stock_count' => $lowStockCount,
            'expiring_count' => $expiringCount,
            'total_value' => $totalValue,
        ];
    }

    /**
     * Productos con menor rotación (stock muerto)
     */
    public function getSlowMovingProducts(int $userId, int $limit = 5): array
    {
        return ProductStock::where('user_id', $userId)
            ->where('active', true)
            ->where('quantity', '>', 0)
            ->with('product')
            ->withCount(['movements as last_movement_days' => function($query) {
                $query->selectRaw('DATEDIFF(NOW(), MAX(created_at))');
            }])
            ->orderByDesc('last_movement_days')
            ->limit($limit)
            ->get()
            ->map(function($stock) {
                return [
                    'product' => $stock->product->name,
                    'quantity' => (float) $stock->quantity,
                    'unit' => $stock->unit,
                    'days_without_movement' => $stock->last_movement_days ?? 0,
                    'value' => (float) $stock->quantity * ($stock->unit_price ?? 0),
                ];
            })
            ->toArray();
    }
}
