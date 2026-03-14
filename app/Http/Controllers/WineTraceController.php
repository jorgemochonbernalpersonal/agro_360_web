<?php

namespace App\Http\Controllers;

use App\Models\Wine;

class WineTraceController extends Controller
{
    public function show(string $token)
    {
        $wine = Wine::where('trace_token', $token)
            ->with([
                'wineHarvests.harvest.plotPlanting.plotVariety.grapeVariety',
                'analyses' => fn($q) => $q->latest('analysis_date')->take(1),
            ])
            ->firstOrFail();

        $composition = $wine->wineHarvests;
        $lastAnalysis = $wine->analyses->first();

        return view('public.wine-trace', compact('wine', 'composition', 'lastAnalysis'));
    }
}
