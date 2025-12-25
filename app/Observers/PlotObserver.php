<?php

namespace App\Observers;

use App\Models\Plot;
use App\Models\PlotAuditLog;

class PlotObserver
{
    /**
     * Handle the Plot "created" event.
     */
    public function created(Plot $plot): void
    {
        PlotAuditLog::log($plot, 'created', [
            'new' => $plot->only([
                'name',
                'surface_area',
                'location',
                'cadastral_reference',
                'province_id',
                'municipality_id',
                'autonomous_community_id',
            ]),
        ]);
    }

    /**
     * Handle the Plot "updated" event.
     */
    public function updated(Plot $plot): void
    {
        $changes = [];
        $trackedFields = [
            'name',
            'surface_area',
            'location',
            'cadastral_reference',
            'province_id',
            'municipality_id',
            'autonomous_community_id',
        ];

        foreach ($trackedFields as $field) {
            if ($plot->isDirty($field)) {
                $changes['old'][$field] = $plot->getOriginal($field);
                $changes['new'][$field] = $plot->$field;
            }
        }

        if (!empty($changes)) {
            PlotAuditLog::log($plot, 'updated', $changes);
        }
    }

    /**
     * Handle the Plot "deleted" event.
     */
    public function deleted(Plot $plot): void
    {
        PlotAuditLog::log($plot, 'deleted', [
            'old' => $plot->only([
                'name',
                'surface_area',
                'location',
            ]),
        ]);
    }
}
