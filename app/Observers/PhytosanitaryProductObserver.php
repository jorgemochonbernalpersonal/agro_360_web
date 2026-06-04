<?php

namespace App\Observers;

use App\Models\OnboardingProgress;
use App\Models\PhytosanitaryProduct;

class PhytosanitaryProductObserver
{
    public function created(PhytosanitaryProduct $product): void
    {
        if (! $product->user_id) {
            return;
        }

        $progress = OnboardingProgress::getOrCreate(
            $product->user_id,
            OnboardingProgress::STEP_ADD_PRODUCTS
        );

        if (! $progress->isCompleted()) {
            $progress->markAsCompleted();
        }
    }
}
