<?php

namespace App\Livewire\Supervisor\Territory;

use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    public string $activeTab = 'provinces';

    protected $queryString = [
        'activeTab' => ['except' => 'provinces', 'as' => 'tab'],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        // Surface by province
        $byProvince = DB::table('plots')
            ->join('provinces', 'provinces.id', '=', 'plots.province_id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->where('plots.active', true)
            ->select(
                'provinces.name as province_name',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(plots.area), 0) as total_area'),
                DB::raw('SUM(CASE WHEN plots.is_organic = 1 THEN 1 ELSE 0 END) as organic_count')
            )
            ->groupBy('provinces.id', 'provinces.name')
            ->orderByDesc('total_area')
            ->get();

        // Surface by grape variety (via plot_plantings)
        $byVariety = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->join('grape_varieties', 'grape_varieties.id', '=', 'plot_plantings.grape_variety_id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->where('plots.active', true)
            ->where('plot_plantings.status', 'active')
            ->select(
                'grape_varieties.name as variety_name',
                'grape_varieties.color as variety_color',
                DB::raw('COUNT(DISTINCT plots.id) as plot_count'),
                DB::raw('COALESCE(SUM(plot_plantings.area_planted), 0) as planted_area')
            )
            ->groupBy('grape_varieties.id', 'grape_varieties.name', 'grape_varieties.color')
            ->orderByDesc('planted_area')
            ->get();

        // Surface by municipality (top 20)
        $byMunicipality = DB::table('plots')
            ->join('municipalities', 'municipalities.id', '=', 'plots.municipality_id')
            ->whereIn('plots.viticulturist_id', $viticulturistIds)
            ->where('plots.active', true)
            ->select(
                'municipalities.name as municipality_name',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(plots.area), 0) as total_area')
            )
            ->groupBy('municipalities.id', 'municipalities.name')
            ->orderByDesc('total_area')
            ->limit(20)
            ->get();

        $totalArea    = (float) $byProvince->sum('total_area');
        $totalPlots   = (int)   $byProvince->sum('plot_count');
        $organicPlots = (int)   $byProvince->sum('organic_count');

        $tabs = [
            'provinces'     => ['label' => 'Por provincia',   'count' => $byProvince->count()],
            'varieties'     => ['label' => 'Por variedad',    'count' => $byVariety->count()],
            'municipalities'=> ['label' => 'Por municipio',   'count' => $byMunicipality->count()],
        ];

        return view('livewire.supervisor.territory.index', [
            'tabs'            => $tabs,
            'byProvince'      => $byProvince,
            'byVariety'       => $byVariety,
            'byMunicipality'  => $byMunicipality,
            'totalArea'       => $totalArea,
            'totalPlots'      => $totalPlots,
            'organicPlots'    => $organicPlots,
        ]);
    }
}
