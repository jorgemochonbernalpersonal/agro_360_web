<?php

namespace App\Livewire\Winery\Silicie;

use App\Services\WineryInfoviService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * INFOVI — Sistema de Información de Mercados del Sector Vitivinícola
 *
 * Governed by Real Decreto 739/2015 (MAPA/AICA).
 * Producers must declare monthly (≥1,000 HL avg) or twice yearly (<1,000 HL avg):
 * stocks, production, and sales broken down by wine type and DO/IGP/table.
 *
 * Campaign year: August 1 → July 31.
 * This component generates the data in HL (hectolitres) needed to fill
 * the official AICA declarations at mapa.gob.es/infovi.
 */
class Infovi extends Component
{

    public string $filterCampaign = '';

    public bool $showCategoryBreakdown = false;

    public function mount(): void
    {
        $now = now();
        $this->filterCampaign = (string) ($now->month >= 8 ? $now->year : $now->year - 1);
    }

    public function toggleCategoryBreakdown(): void
    {
        $this->showCategoryBreakdown = ! $this->showCategoryBreakdown;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $wineryId = Auth::id();
        $campaign = (int) ($this->filterCampaign ?: (now()->month >= 8 ? now()->year : now()->year - 1));

        $campaignStart = "{$campaign}-08-01";
        $campaignEnd = ($campaign + 1).'-07-31';

        $org = Auth::user()->organization;
        $service = app(WineryInfoviService::class);
        $threshold = $service->buildThreshold($wineryId);

        $existencias = $service->buildCuadroExistencias($wineryId, $campaign, $this->showCategoryBreakdown);
        $produccion = $service->buildCuadroProduccion($wineryId, $campaign, $this->showCategoryBreakdown);
        $ventas = $service->buildCuadroVentas($wineryId, $campaignStart, $campaignEnd);
        $entradas = $service->buildCuadroEntradas($wineryId, $campaign, $campaignStart, $campaignEnd);
        $balanceSheet = $service->buildBalanceSheet($wineryId, $campaign, $campaignStart, $campaignEnd);
        $mosto = $service->buildCuadroMosto($wineryId, $campaign, $campaignStart, $campaignEnd);

        $campaigns = DB::table('harvests')
            ->where('winery_id', $wineryId)
            ->selectRaw('DISTINCT vintage')
            ->orderByDesc('vintage')
            ->pluck('vintage')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values();

        if ($campaigns->isEmpty()) {
            $campaigns = collect([now()->month >= 8 ? now()->year : now()->year - 1]);
        }

        return view('livewire.winery.silicie.infovi', compact(
            'existencias', 'produccion', 'ventas', 'entradas',
            'balanceSheet', 'mosto',
            'campaign', 'campaignStart', 'campaignEnd',
            'org', 'threshold', 'campaigns'
        ));
    }
}
