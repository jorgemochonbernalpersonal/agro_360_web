<?php

namespace App\Livewire\Concerns;

use App\Models\AutonomousCommunity;
use App\Models\Municipality;
use App\Models\Plot;
use App\Models\Province;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait WithGeoFiltering
{
    protected function geoCachePrefix(): string
    {
        return 'filter';
    }

    public function getAutonomousCommunitiesProperty()
    {
        $key = $this->geoCachePrefix().'_autonomous_communities';

        return Cache::remember($key, now()->addHours(24), function () {
            $plotIds = Plot::forUser(Auth::user())->pluck('id');

            return AutonomousCommunity::whereHas('plots', fn ($q) => $q->whereIn('plots.id', $plotIds))
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($ca) => [$ca->id => $ca->name]);
        });
    }

    public function getProvincesProperty()
    {
        if (! $this->filterAutonomousCommunity) {
            return collect();
        }

        $key = $this->geoCachePrefix()."_provinces_{$this->filterAutonomousCommunity}";

        return Cache::remember($key, now()->addHours(24), function () {
            $plotIds = Plot::forUser(Auth::user())->pluck('id');

            return Province::where('autonomous_community_id', $this->filterAutonomousCommunity)
                ->whereHas('plots', fn ($q) => $q->whereIn('plots.id', $plotIds))
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($prov) => [$prov->id => $prov->name]);
        });
    }

    public function getMunicipalitiesProperty()
    {
        if (! $this->filterProvince) {
            return collect();
        }

        $key = $this->geoCachePrefix()."_municipalities_{$this->filterProvince}";

        return Cache::remember($key, now()->addHours(24), function () {
            $plotIds = Plot::forUser(Auth::user())->pluck('id');

            return Municipality::where('province_id', $this->filterProvince)
                ->whereHas('plots', fn ($q) => $q->whereIn('plots.id', $plotIds))
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn ($mun) => [$mun->id => $mun->name]);
        });
    }
}
