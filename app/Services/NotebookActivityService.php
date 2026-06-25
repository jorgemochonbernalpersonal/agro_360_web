<?php

namespace App\Services;

use App\Models\AgriculturalActivity;
use Illuminate\Support\Facades\DB;

class NotebookActivityService
{
    /**
     * Crea una actividad agrícola y el modelo específico asociado en una transacción.
     * $createEntry recibe el ID de la actividad creada y crea el modelo específico.
     */
    public function createActivity(array $activityData, callable $createEntry): AgriculturalActivity
    {
        return DB::transaction(function () use ($activityData, $createEntry) {
            $activity = AgriculturalActivity::create($activityData);
            $createEntry($activity->id);
            return $activity;
        });
    }

    /**
     * Actualiza una actividad agrícola y el modelo específico asociado en una transacción.
     * $updateEntry actualiza el modelo específico (recibe la actividad actualizada).
     */
    public function updateActivity(AgriculturalActivity $activity, array $activityData, callable $updateEntry): void
    {
        DB::transaction(function () use ($activity, $activityData, $updateEntry) {
            $activity->update($activityData);
            $updateEntry($activity);
        });
    }
}
