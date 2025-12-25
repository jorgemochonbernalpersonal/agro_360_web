<?php

namespace App\Observers;

use App\Models\PlotPlanting;
use App\Models\PlotPlantingAuditLog;

class PlotPlantingObserver
{
    /**
     * Handle the PlotPlanting "created" event.
     */
    public function created(PlotPlanting $planting): void
    {
        PlotPlantingAuditLog::log($planting, 'created', [
            'new' => $planting->only([
                'name',
                'grape_variety_id',
                'area_planted',
                'planting_year',
                'planting_date',
                'vine_count',
                'density',
                'irrigated',
                'planting_authorization',
                'authorization_date',
                'right_type',
                'designation_of_origin',
            ]),
        ]);
    }

    /**
     * Handle the PlotPlanting "updated" event.
     */
    public function updated(PlotPlanting $planting): void
    {
        $changes = [];
        $trackedFields = [
            'name',
            'grape_variety_id',
            'area_planted',
            'planting_year',
            'planting_date',
            'vine_count',
            'density',
            'row_spacing',
            'vine_spacing',
            'rootstock',
            'training_system_id',
            'irrigated',
            'status',
            'planting_authorization',
            'authorization_date',
            'right_type',
            'uprooting_date',
            'designation_of_origin',
        ];

        foreach ($trackedFields as $field) {
            if ($planting->isDirty($field)) {
                $changes['old'][$field] = $planting->getOriginal($field);
                $changes['new'][$field] = $planting->$field;
            }
        }

        if (!empty($changes)) {
            PlotPlantingAuditLog::log($planting, 'updated', $changes);
        }
    }

    /**
     * Handle the PlotPlanting "deleted" event.
     */
    public function deleted(PlotPlanting $planting): void
    {
        PlotPlantingAuditLog::log($planting, 'deleted', [
            'old' => $planting->only([
                'name',
                'grape_variety_id',
                'area_planted',
                'planting_year',
            ]),
        ]);
    }
}
