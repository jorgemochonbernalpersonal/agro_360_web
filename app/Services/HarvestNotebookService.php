<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\PhenologyObservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HarvestNotebookService
{
    public function create(array $activityData, array $harvestData): Harvest
    {
        return DB::transaction(function () use ($activityData, $harvestData) {
            $activity = AgriculturalActivity::create($activityData);

            $harvest = Harvest::create(array_merge($harvestData, [
                'activity_id' => $activity->id,
                'status' => 'active',
            ]));

            $this->syncPhenology(
                $harvestData['plot_planting_id'],
                $harvestData['harvest_start_date'],
                $activity->campaign_id,
                $activityData['viticulturist_id']
            );

            return $harvest;
        });
    }

    public function update(Harvest $harvest, array $activityData, array $harvestData, User $user): void
    {
        DB::transaction(function () use ($harvest, $activityData, $harvestData, $user) {
            $harvest->activity->update($activityData);

            $this->swapContainer($harvest, $harvestData['container_id'] ?? null, $user->id);

            $harvest->update($harvestData);

            $this->syncPhenology(
                $harvestData['plot_planting_id'],
                $harvestData['harvest_start_date'],
                $harvest->activity->campaign_id,
                $user->id
            );
        });
    }

    private function swapContainer(Harvest $harvest, mixed $newContainerId, int $userId): void
    {
        $originalContainerId = $harvest->container_id;
        if ($newContainerId == $originalContainerId) {
            return;
        }

        if ($originalContainerId) {
            Container::where('id', $originalContainerId)->where('user_id', $userId)->update(['harvest_id' => null]);
        }

        if ($newContainerId) {
            Container::where('id', $newContainerId)->where('user_id', $userId)->update(['harvest_id' => $harvest->id]);
        }
    }

    private function syncPhenology(int $plotPlantingId, string $harvestStartDate, int $campaignId, int $viticulturistId): void
    {
        PhenologyObservation::updateOrCreate(
            ['plot_planting_id' => $plotPlantingId, 'campaign_id' => $campaignId, 'event' => 'harvest'],
            ['viticulturist_id' => $viticulturistId, 'obs_date' => $harvestStartDate, 'bbch_code' => 89, 'source' => 'manual', 'active' => true]
        );
    }
}
