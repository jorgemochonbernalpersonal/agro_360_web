<?php

namespace App\Livewire\Viticulturist\DigitalNotebook;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Plot;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

abstract class AbstractActivityIndex extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab = 'unlocked'; // 'unlocked' | 'locked'

    public string $search = '';

    public string $plotFilter = '';

    public string $campaignFilter = '';

    protected $queryString = [
        'currentTab' => ['as' => 'tab',      'except' => 'unlocked'],
        'search' => ['except' => ''],
        'plotFilter' => ['except' => ''],
        'campaignFilter' => ['except' => ''],
    ];

    // ── Lifecycle ─────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (! Auth::user()->can('viewAny', AgriculturalActivity::class)) {
            abort(403, __('No tienes permiso para ver actividades agrícolas.'));
        }

        // Pre-select the active campaign
        if (! $this->campaignFilter) {
            $campaign = Campaign::forViticulturist(Auth::id())
                ->where('active', true)
                ->first();
            if ($campaign) {
                $this->campaignFilter = (string) $campaign->id;
            }
        }
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPlotFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCampaignFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->plotFilter = '';
        $this->campaignFilter = '';
        $this->resetPage();
    }

    public function delete(int $activityId): void
    {
        $activity = AgriculturalActivity::forViticulturist(Auth::id())->findOrFail($activityId);

        if (! Auth::user()->can('delete', $activity)) {
            $this->toastError(__('No tienes permiso para eliminar esta actividad.'));

            return;
        }

        if ($activity->isLocked()) {
            $this->toastError(__('No se puede eliminar una actividad bloqueada. Las actividades se bloquean automáticamente para cumplimiento PAC.'));

            return;
        }

        try {
            $activity->delete();
            $this->toastSuccess(__('Actividad eliminada correctamente.'));
        } catch (\Exception $e) {
            \Log::error('Error al eliminar actividad', [
                'error' => $e->getMessage(),
                'activity_id' => $activityId,
                'user_id' => Auth::id(),
            ]);
            $this->toastError(__('Error al eliminar la actividad. Por favor, intenta de nuevo.'));
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $user = Auth::user();

        $query = AgriculturalActivity::forViticulturist($user->id)
            ->ofType($this->activityType())
            ->with($this->eagerLoadRelations())
            ->orderBy('activity_date', 'desc');

        // Tab filter
        $query->where('is_locked', $this->currentTab === 'locked');

        // Campaign filter
        if ($this->campaignFilter) {
            $query->forCampaign((int) $this->campaignFilter);
        }

        // Plot filter
        if ($this->plotFilter) {
            $query->forPlot((int) $this->plotFilter);
        }

        // Search
        if ($this->search) {
            $s = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($s) {
                $q->whereRaw('LOWER(notes) LIKE ?', [$s])
                    ->orWhereHas('plot', fn ($pq) => $pq->whereRaw('LOWER(name) LIKE ?', [$s]));
            });
        }

        $activities = $query->paginate(12);

        // Tab counts (respects campaign filter, ignores tab & plot & search)
        $statsQuery = AgriculturalActivity::forViticulturist($user->id)
            ->ofType($this->activityType());
        if ($this->campaignFilter) {
            $statsQuery->forCampaign((int) $this->campaignFilter);
        }
        $statsRow = $statsQuery->selectRaw(
            'SUM(CASE WHEN is_locked = 0 THEN 1 ELSE 0 END) as unlocked,
             SUM(CASE WHEN is_locked = 1 THEN 1 ELSE 0 END) as locked'
        )->first();

        $stats = [
            'unlocked' => (int) ($statsRow->unlocked ?? 0),
            'locked' => (int) ($statsRow->locked ?? 0),
        ];

        $plots = Plot::forUser($user)
            ->where('active', true)
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get();

        $campaigns = Campaign::forViticulturist($user->id)
            ->orderBy('year', 'desc')
            ->get();

        return view('livewire.viticulturist.digital-notebook.activity-index', [
            'activities' => $activities,
            'stats' => $stats,
            'plots' => $plots,
            'campaigns' => $campaigns,
            'pageTitle' => $this->pageTitle(),
            'pageDescription' => $this->pageDescription(),
            'createRoute' => $this->createRoute(),
            'editRouteName' => $this->editRouteName(),
            'activityType' => $this->activityType(),
            'typeIcon' => $this->typeIcon(),
            'typeBg' => $this->typeBgClass(),
            'typeIconColor' => $this->typeIconColorClass(),
            'typeBadgeColor' => $this->typeBadgeColor(),
        ])->layout('layouts.app', [
            'title' => $this->pageTitle().' - Agro365',
            'description' => $this->pageDescription(),
        ]);
    }

    // ── Subclass contract ─────────────────────────────────────────────────────

    abstract protected function activityType(): string;

    abstract protected function pageTitle(): string;

    abstract protected function pageDescription(): string;

    abstract protected function createRoute(): string;

    abstract protected function editRouteSuffix(): string;

    protected function editRouteName(): string
    {
        $prefix = Auth::user()?->isProducer() ? 'producer' : 'viticulturist';

        return "{$prefix}.{$this->editRouteSuffix()}";
    }

    abstract protected function typeIcon(): string;

    abstract protected function typeBadgeColor(): string;

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function eagerLoadRelations(): array
    {
        $base = ['plot', 'plotPlanting.grapeVariety', 'crew', 'campaign'];

        return array_merge($base, match ($this->activityType()) {
            'phytosanitary' => ['phytosanitaryTreatment.product', 'phytosanitaryTreatment.pest'],
            'fertilization' => ['fertilization'],
            'irrigation' => ['irrigation'],
            'cultural' => ['culturalWork'],
            'observation' => ['observation'],
            'harvest' => ['harvest'],
            'pruning' => ['culturalWork'],
            'post_harvest' => ['postHarvestTreatment.product'],
            default => [],
        });
    }

    protected function typeBgClass(): string
    {
        return match ($this->typeBadgeColor()) {
            'red' => 'bg-red-50',
            'blue' => 'bg-blue-50',
            'cyan' => 'bg-cyan-50',
            'yellow' => 'bg-amber-50',
            'lime' => 'bg-lime-50',
            'indigo' => 'bg-indigo-50',
            'purple' => 'bg-purple-50',
            default => 'bg-zinc-100',
        };
    }

    protected function typeIconColorClass(): string
    {
        return match ($this->typeBadgeColor()) {
            'red' => 'text-red-600',
            'blue' => 'text-blue-600',
            'cyan' => 'text-cyan-600',
            'yellow' => 'text-amber-600',
            'lime' => 'text-lime-600',
            'indigo' => 'text-indigo-600',
            'purple' => 'text-purple-600',
            default => 'text-zinc-500',
        };
    }
}
