<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\PlotPlanting;
use App\Notifications\QualityFeedbackNotification;
use Illuminate\Support\Facades\DB;

class GrapeReceptionService
{
    /**
     * Crea una nueva recepción de uva dentro de una transacción.
     * Hace firstOrCreate del batch, crea el Harvest, incrementa el acumulado
     * y notifica al viticultor si hay datos de calidad.
     *
     * @throws \RuntimeException si la campaña no se puede obtener o el batch está cerrado
     */
    public function createReception(
        int $wineryId,
        int $viticulturistId,
        int $vintageYear,
        PlotPlanting $planting,
        array $harvestData,
        string $wineryName
    ): Harvest {
        $campaign = Campaign::getOrCreateActiveForYear($wineryId, $vintageYear);
        if (! $campaign) {
            throw new \RuntimeException('No se pudo obtener la campaña activa.');
        }

        $existingBatch = GrapeReceptionBatch::where('winery_id', $wineryId)
            ->where('plot_planting_id', $planting->id)
            ->where('campaign_id', $campaign->id)
            ->first();

        if ($existingBatch && $existingBatch->status === 'closed') {
            throw new \RuntimeException('El lote de esta plantación está cerrado. Réabrelo desde el Cuadro de Mando antes de añadir más recepciones.');
        }

        return DB::transaction(function () use ($wineryId, $viticulturistId, $vintageYear, $planting, $campaign, $harvestData, $wineryName) {
            $batch = GrapeReceptionBatch::firstOrCreate(
                [
                    'winery_id' => $wineryId,
                    'plot_planting_id' => $planting->id,
                    'campaign_id' => $campaign->id,
                ],
                [
                    'viticulturist_id' => $viticulturistId,
                    'vintage_year' => $vintageYear,
                    'total_weight_kg' => 0,
                    'designation_of_origin' => $planting->designation_of_origin,
                    'status' => 'open',
                ]
            );

            $harvest = Harvest::create(array_merge($harvestData, [
                'winery_id' => $wineryId,
                'batch_id' => $batch->id,
                'status' => 'active',
                'destination_type' => 'winery',
            ]));

            $batch->increment('total_weight_kg', $harvestData['total_weight']);

            $this->notifyQualityIfNeeded($harvest, $batch, $wineryName, true, $harvestData);

            return $harvest;
        });
    }

    /**
     * Actualiza una recepción existente, recalcula el acumulado del batch
     * y notifica al viticultor si los datos de calidad cambiaron.
     */
    public function updateReception(
        Harvest $harvest,
        array $harvestData,
        float $oldWeight,
        int $wineryId,
        string $wineryName
    ): void {
        DB::transaction(function () use ($harvest, $harvestData, $oldWeight, $wineryId, $wineryName) {
            $harvest->update($harvestData);

            if ($oldWeight !== (float) $harvestData['total_weight'] && $harvest->batch_id) {
                $harvest->batch?->recalculateTotal();
            }

            $qualityChanged = $harvest->wasChanged(['baume_degree', 'potential_alcohol', 'acidity_level', 'ph_level']);
            $this->notifyQualityIfNeeded($harvest, $harvest->batch, $wineryName, $qualityChanged, $harvestData);
        });
    }

    private function notifyQualityIfNeeded(
        Harvest $harvest,
        ?GrapeReceptionBatch $batch,
        string $wineryName,
        bool $qualityDataPresent,
        array $harvestData
    ): void {
        if (! $qualityDataPresent) {
            return;
        }

        $hasQuality = ($harvestData['baume_degree'] ?? null)
            || ($harvestData['potential_alcohol'] ?? null)
            || ($harvestData['acidity_level'] ?? null)
            || ($harvestData['ph_level'] ?? null);

        $isExternalViticulturist = $batch?->viticulturist_id
            && $batch->viticulturist_id !== $harvest->winery_id;

        if (! $hasQuality || ! $isExternalViticulturist) {
            return;
        }

        $viticulturist = \App\Models\User::find($batch->viticulturist_id);
        if ($viticulturist?->can_login) {
            $viticulturist->notify(new QualityFeedbackNotification(
                $harvest->load('plotPlanting.grapeVariety', 'plotPlanting.plot'),
                $wineryName,
            ));
        }
    }
}
