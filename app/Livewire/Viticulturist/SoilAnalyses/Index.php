<?php

namespace App\Livewire\Viticulturist\SoilAnalyses;

use App\Livewire\Viticulturist\AbstractIndex;
use App\Models\Campaign;
use App\Models\Plot;
use App\Models\SoilAnalysis;
use Illuminate\Database\Eloquent\Builder;

class Index extends AbstractIndex
{
    public string $search = '';

    public string $filterPlot = '';

    public string $filterCampaign = '';

    public string $filterTexture = '';

    protected $queryString = [
        'search' => ['as' => 'q',        'except' => ''],
        'filterPlot' => ['as' => 'plot',     'except' => ''],
        'filterCampaign' => ['as' => 'campaign', 'except' => ''],
        'filterTexture' => ['as' => 'texture',  'except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlot(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCampaign(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTexture(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $this->findOwned(SoilAnalysis::class, $id)->delete();
        $this->toastSuccess(__('Análisis de suelo eliminado.'));
    }

    protected function filterDefaults(): array
    {
        return [
            'search' => '',
            'filterPlot' => '',
            'filterCampaign' => '',
            'filterTexture' => '',
        ];
    }

    protected function baseQuery(): Builder
    {
        return SoilAnalysis::where('viticulturist_id', $this->viticulturistId())
            ->with('plot', 'campaign');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('laboratory', 'like', '%'.$this->search.'%')
                    ->orWhere('notes', 'like', '%'.$this->search.'%');
            });
        }
        if ($this->filterPlot) {
            $query->where('plot_id', $this->filterPlot);
        }
        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterTexture) {
            $query->where('texture_class', $this->filterTexture);
        }
    }

    protected function defaultOrderBy(): array
    {
        return ['analysis_date', 'desc'];
    }

    protected function perPage(): int
    {
        return 15;
    }

    protected function viewData(mixed $entries): array
    {
        $userId = $this->viticulturistId();
        $baseQuery = SoilAnalysis::where('viticulturist_id', $userId);

        $stats = [
            'total' => (clone $baseQuery)->count(),
            'plots_analyzed' => (clone $baseQuery)->distinct('plot_id')->count('plot_id'),
            'avg_ph' => round((float) (clone $baseQuery)->whereNotNull('ph')->avg('ph'), 2),
            'avg_organic_matter' => round((float) (clone $baseQuery)->whereNotNull('organic_matter')->avg('organic_matter'), 2),
        ];

        // Derive pH label and color for the average
        $avgPhRange = null;
        if ($stats['avg_ph'] > 0) {
            foreach (SoilAnalysis::PH_RANGES as $range) {
                if ($stats['avg_ph'] >= $range['min'] && $stats['avg_ph'] < $range['max']) {
                    $avgPhRange = $range;
                    break;
                }
            }
        }
        $stats['avg_ph_label'] = $avgPhRange['label'] ?? '—';
        $stats['avg_ph_color'] = $avgPhRange['color'] ?? 'zinc';

        return [
            'entries' => $entries,
            'stats' => $stats,
            'textureClasses' => SoilAnalysis::textureClassOptions(),
            'plots' => Plot::where('viticulturist_id', $userId)->orderBy('name')->get(),
            'campaigns' => Campaign::forViticulturist($userId)->orderBy('year', 'desc')->get(),
        ];
    }
}
