<?php

namespace App\Observers;

use App\Models\Campaign;
use App\Models\OnboardingProgress;

class CampaignObserver
{
    /**
     * Handle the Campaign "created" event.
     */
    public function created(Campaign $campaign): void
    {
        // Marcar paso de onboarding como completado cuando se crea/revisa la campaña
        if ($campaign->viticulturist_id) {
            $progress = OnboardingProgress::getOrCreate(
                $campaign->viticulturist_id,
                OnboardingProgress::STEP_REVIEW_CAMPAIGN
            );
            
            if (!$progress->isCompleted()) {
                $progress->markAsCompleted();
            }
        }
    }
}
