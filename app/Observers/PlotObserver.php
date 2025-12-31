<?php

namespace App\Observers;

use App\Models\Plot;
use App\Models\OnboardingProgress;

class PlotObserver
{
    /**
     * Handle the Plot "created" event.
     */
    public function created(Plot $plot): void
    {
        // Marcar paso de onboarding como completado
        if ($plot->viticulturist_id) {
            $progress = OnboardingProgress::getOrCreate(
                $plot->viticulturist_id,
                OnboardingProgress::STEP_CREATE_PLOT
            );
            
            if (!$progress->isCompleted()) {
                $progress->markAsCompleted();
            }
        }
    }

    /**
     * Handle the Plot "updated" event.
     */
    public function updated(Plot $plot): void
    {
        //
    }

    /**
     * Handle the Plot "deleted" event.
     */
    public function deleted(Plot $plot): void
    {
        //
    }

    /**
     * Handle the Plot "restored" event.
     */
    public function restored(Plot $plot): void
    {
        //
    }

    /**
     * Handle the Plot "force deleted" event.
     */
    public function forceDeleted(Plot $plot): void
    {
        //
    }
}
