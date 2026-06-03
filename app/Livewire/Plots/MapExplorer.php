<?php

namespace App\Livewire\Plots;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\MultipartPlotSigpac;
use App\Models\Plot;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class MapExplorer extends Component
{
    public string $communityId  = '';
    public string $provinceId   = '';
    public string $municipalityId = '';

    public function updatedCommunityId(): void
    {
        $this->provinceId     = '';
        $this->municipalityId = '';
    }

    public function updatedProvinceId(): void
    {
        $this->municipalityId = '';
    }

    public function viewMap(): void
    {
        if (!$this->municipalityId) {
            return;
        }
        $this->redirect(route('map.municipality', $this->municipalityId), navigate: true);
    }

    public function render()
    {
        $user = Auth::user();

        // Parcelas del usuario que ya tienen geometría generada
        $plotsWithGeo = Plot::forUser($user)
            ->whereHas('multiplePlotSigpacs', fn($q) => $q->whereNotNull('plot_geometry_id'))
            ->get(['id', 'autonomous_community_id', 'province_id', 'municipality_id']);

        $communityIds = $plotsWithGeo->pluck('autonomous_community_id')->unique()->filter();
        $communities  = AutonomousCommunity::whereIn('id', $communityIds)->orderBy('name')->get();

        $provinceIds = $this->communityId
            ? $plotsWithGeo->where('autonomous_community_id', $this->communityId)
                           ->pluck('province_id')->unique()->filter()
            : collect();
        $provinces = $provinceIds->isNotEmpty()
            ? Province::whereIn('id', $provinceIds)->orderBy('name')->get()
            : collect();

        $municipalityIds = $this->provinceId
            ? $plotsWithGeo->where('province_id', $this->provinceId)
                           ->pluck('municipality_id')->unique()->filter()
            : collect();
        $municipalities = $municipalityIds->isNotEmpty()
            ? Municipality::whereIn('id', $municipalityIds)->orderBy('name')->get()
            : collect();

        // Conteo de recintos con mapa para el municipio seleccionado
        $geometryCount = null;
        $plotCount     = null;
        if ($this->municipalityId) {
            $plotIds = Plot::forUser($user)
                ->where('municipality_id', $this->municipalityId)
                ->pluck('id');
            $plotCount     = $plotIds->count();
            $geometryCount = MultipartPlotSigpac::whereIn('plot_id', $plotIds)
                ->whereNotNull('plot_geometry_id')
                ->count();
        }

        return view('livewire.plots.map-explorer', compact(
            'communities', 'provinces', 'municipalities', 'geometryCount', 'plotCount'
        ))->layout('layouts.app', [
            'title'       => __('Explorador de Mapas') . ' - Agro365',
            'description' => __('Visualiza todas tus parcelas en el mapa filtradas por territorio'),
        ]);
    }
}
