<?php

namespace App\Observers;

use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Cache;

class WineryViticulturistObserver
{
    public function created(WineryViticulturist $rel): void
    {
        if ($rel->winery_id) {
            Cache::forget("winery:{$rel->winery_id}:has_viticulturists");
        }
    }

    public function deleted(WineryViticulturist $rel): void
    {
        if ($rel->winery_id) {
            Cache::forget("winery:{$rel->winery_id}:has_viticulturists");
        }
    }
}
