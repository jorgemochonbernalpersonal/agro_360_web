<?php

namespace App\Services;

use App\Models\WineBottling;
use Illuminate\Support\Facades\DB;

class WineBottlingService
{
    public function createBottling(int $userId, array $bottlingData, array $supplies): WineBottling
    {
        return DB::transaction(function () use ($userId, $bottlingData, $supplies) {
            $bottling = WineBottling::create(array_merge($bottlingData, [
                'user_id' => $userId,
                'created_by' => $userId,
            ]));

            app(WineContainerStockService::class)->recordBottling($bottling);

            $this->syncSupplies($bottling, $supplies);

            return $bottling;
        });
    }

    public function updateBottling(WineBottling $bottling, array $bottlingData, array $supplies, array $oldBottlingData): void
    {
        DB::transaction(function () use ($bottling, $bottlingData, $supplies, $oldBottlingData) {
            $bottling->update($bottlingData);
            $bottling->refresh();

            app(WineContainerStockService::class)->updateBottling($bottling, $oldBottlingData);

            $this->syncSupplies($bottling, $supplies);
        });
    }

    private function syncSupplies(WineBottling $bottling, array $supplies): void
    {
        $bottling->supplies()->delete();

        foreach ($supplies as $row) {
            if (empty($row['supply_name']) && empty($row['winery_supply_id'])) {
                continue;
            }

            $bottling->supplies()->create([
                'winery_supply_id' => $row['winery_supply_id'] ?: null,
                'supply_name' => $row['supply_name'] ?: '',
                'quantity' => $row['quantity'] ?: 0,
                'unit_of_measurement_id' => $row['unit_of_measurement_id'] ?: null,
                'notes' => $row['notes'] ?: null,
            ]);
        }
    }
}
