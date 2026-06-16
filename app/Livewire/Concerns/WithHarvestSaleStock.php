<?php

namespace App\Livewire\Concerns;

use App\Models\Harvest;
use App\Models\HarvestStock;
use Illuminate\Support\Facades\Auth;

trait WithHarvestSaleStock
{
    /**
     * Get current stock state for a harvest (at harvest level, container_id=null).
     * If no HarvestStock events exist, available = harvest.total_weight.
     */
    private function getHarvestStockState(int $harvestId): array
    {
        $latest = HarvestStock::where('harvest_id', $harvestId)
            ->whereNull('container_id')
            ->orderByDesc('id')
            ->first();

        if (! $latest) {
            $harvest = Harvest::find($harvestId);
            $total = (float) ($harvest->total_weight ?? 0);

            return [
                'available' => $total,
                'reserved' => 0.0,
                'sold' => 0.0,
                'gifted' => 0.0,
                'lost' => 0.0,
                'total' => $total,
            ];
        }

        return [
            'available' => (float) $latest->available_qty,
            'reserved' => (float) $latest->reserved_qty,
            'sold' => (float) $latest->sold_qty,
            'gifted' => (float) $latest->gifted_qty,
            'lost' => (float) $latest->lost_qty,
            'total' => (float) $latest->quantity_after,
        ];
    }

    /**
     * available → reserved  (when creating an invoice)
     */
    private function reserveHarvestStock(int $harvestId, float $qty, int $invoiceId): void
    {
        $s = $this->getHarvestStockState($harvestId);

        if ($s['available'] < $qty - 0.001) {
            throw new \RuntimeException(
                "Stock insuficiente en cosecha #{$harvestId}. Disponible: {$s['available']} kg, solicitado: {$qty} kg."
            );
        }

        HarvestStock::create([
            'harvest_id' => $harvestId,
            'container_id' => null,
            'user_id' => Auth::id(),
            'movement_type' => 'reserve',
            'quantity_change' => -$qty,
            'quantity_after' => $s['total'],
            'available_qty' => round($s['available'] - $qty, 3),
            'reserved_qty' => round($s['reserved'] + $qty, 3),
            'sold_qty' => $s['sold'],
            'gifted_qty' => $s['gifted'],
            'lost_qty' => $s['lost'],
            'reference_number' => (string) $invoiceId,
            'notes' => "Reservado para factura #{$invoiceId}",
        ]);
    }

    /**
     * reserved → available  (when cancelling an invoice)
     * Also handles partially-delivered invoices by taking from sold if needed.
     */
    private function releaseHarvestStock(int $harvestId, float $qty, int $invoiceId): void
    {
        $s = $this->getHarvestStockState($harvestId);

        $fromReserved = min($qty, $s['reserved']);
        $fromSold = max(0.0, $qty - $fromReserved);

        HarvestStock::create([
            'harvest_id' => $harvestId,
            'container_id' => null,
            'user_id' => Auth::id(),
            'movement_type' => 'unreserve',
            'quantity_change' => $qty,
            'quantity_after' => $s['total'],
            'available_qty' => round($s['available'] + $qty, 3),
            'reserved_qty' => round($s['reserved'] - $fromReserved, 3),
            'sold_qty' => round($s['sold'] - $fromSold, 3),
            'gifted_qty' => $s['gifted'],
            'lost_qty' => $s['lost'],
            'reference_number' => (string) $invoiceId,
            'notes' => "Liberado por cancelación de factura #{$invoiceId}",
        ]);
    }

    /**
     * reserved → sold  (when marking delivery as completed)
     */
    private function sellHarvestStock(int $harvestId, float $qty, int $invoiceId): void
    {
        $s = $this->getHarvestStockState($harvestId);

        if ($s['reserved'] < $qty - 0.001) {
            throw new \RuntimeException(
                "No hay suficiente stock reservado para confirmar la entrega de cosecha #{$harvestId}."
            );
        }

        HarvestStock::create([
            'harvest_id' => $harvestId,
            'container_id' => null,
            'user_id' => Auth::id(),
            'movement_type' => 'sale',
            'quantity_change' => 0,
            'quantity_after' => $s['total'],
            'available_qty' => $s['available'],
            'reserved_qty' => round($s['reserved'] - $qty, 3),
            'sold_qty' => round($s['sold'] + $qty, 3),
            'gifted_qty' => $s['gifted'],
            'lost_qty' => $s['lost'],
            'reference_number' => (string) $invoiceId,
            'notes' => "Entregado en factura #{$invoiceId}",
        ]);
    }
}
