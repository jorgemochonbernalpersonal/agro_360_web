<?php

namespace App\Livewire\Viticulturist\Pac;

use App\Models\Campaign;
use App\Models\PacDeclaration;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $viticulturistId = Auth::id();

        $plots = Plot::where('viticulturist_id', $viticulturistId)
            ->where('active', true)
            ->get();

        $totalArea          = $plots->sum('area');
        $totalEligible      = $plots->sum('pac_eligible_area');
        $totalNonEligible   = $plots->sum('non_eligible_area');
        $plotsWithoutPac    = $plots->whereNull('pac_eligible_area')->count();

        $eligibilityPct = $totalArea > 0
            ? round(($totalEligible / $totalArea) * 100, 1)
            : 0;

        $declarations = PacDeclaration::forViticulturist($viticulturistId)
            ->orderByDesc('year')
            ->get();

        $currentYear     = now()->year;
        $currentDeclaration = $declarations->firstWhere('year', $currentYear);
        $lastDeclaration    = $declarations->first();

        $stats = [
            'total_plots'        => $plots->count(),
            'total_area'         => $totalArea,
            'total_eligible'     => $totalEligible,
            'total_non_eligible' => $totalNonEligible,
            'eligibility_pct'    => $eligibilityPct,
            'plots_without_pac'  => $plotsWithoutPac,
            'total_declarations' => $declarations->count(),
            'approved'           => $declarations->where('status', 'approved')->count(),
            'pending'            => $declarations->whereIn('status', ['draft', 'submitted'])->count(),
        ];

        return view('livewire.viticulturist.pac.dashboard', [
            'stats'              => $stats,
            'currentDeclaration' => $currentDeclaration,
            'lastDeclaration'    => $lastDeclaration,
            'declarations'       => $declarations->take(5),
            'currentYear'        => $currentYear,
            'plotsWithoutPac'    => $plots->whereNull('pac_eligible_area')->values(),
        ])->layout('layouts.app');
    }
}
