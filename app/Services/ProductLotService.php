<?php

namespace App\Services;

use App\Models\ProductLot;
use Illuminate\Support\Facades\DB;

class ProductLotService
{
    public function createLot(int $userId, array $lotData, array $validGrapes): ProductLot
    {
        return DB::transaction(function () use ($userId, $lotData, $validGrapes) {
            $lot = ProductLot::create(array_merge($lotData, [
                'user_id' => $userId,
                'initial_quantity' => $lotData['quantity'],
                'archived' => false,
            ]));

            $this->syncGrapes($lot, $validGrapes);

            return $lot;
        });
    }

    public function updateLot(ProductLot $lot, array $lotData, array $validGrapes): void
    {
        DB::transaction(function () use ($lot, $lotData, $validGrapes) {
            $lot->update($lotData);

            $this->syncGrapes($lot, $validGrapes);
        });
    }

    private function syncGrapes(ProductLot $lot, array $validGrapes): void
    {
        $sync = collect($validGrapes)->mapWithKeys(fn ($g) => [
            $g['grape_variety_id'] => ['percentage' => (float) ($g['percentage'] ?? 0)],
        ])->toArray();

        $lot->grapeVarieties()->sync($sync);
    }
}
