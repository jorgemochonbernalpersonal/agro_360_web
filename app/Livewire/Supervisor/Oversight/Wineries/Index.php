<?php

namespace App\Livewire\Supervisor\Oversight\Wineries;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\SupervisorWinery;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search = '';

    public string $vintageFilter = '';

    // ── Link winery modal ─────────────────────────────────────────────────────
    public bool $showLinkModal = false;

    public string $linkSearch = '';

    public ?int $linkWineryId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'vintageFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVintageFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->vintageFilter = '';
        $this->resetPage();
    }

    // ── Link winery ───────────────────────────────────────────────────────────

    public function openLinkModal(): void
    {
        $this->reset(['linkSearch', 'linkWineryId']);
        $this->resetErrorBag();
        $this->showLinkModal = true;
    }

    public function closeLinkModal(): void
    {
        $this->showLinkModal = false;
    }

    public function updatingLinkSearch(): void
    {
        $this->linkWineryId = null;
    }

    public function linkWinery(): void
    {
        $this->validate([
            'linkWineryId' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value && ! User::where('id', $value)
                        ->whereIn('role', [User::ROLE_WINERY, User::ROLE_PRODUCER])
                        ->exists()) {
                        $fail(__('La bodega seleccionada no es válida.'));
                    }
                },
            ],
        ], [
            'linkWineryId.required' => __('Selecciona una bodega.'),
        ]);

        $doId = Auth::id();
        $winery = User::findOrFail($this->linkWineryId);

        $alreadyLinked = SupervisorWinery::where('supervisor_id', $doId)
            ->where('winery_id', $winery->id)
            ->exists();

        if ($alreadyLinked) {
            $this->toastError("{$winery->name} ya está adscrita a esta denominación.");

            return;
        }

        SupervisorWinery::create([
            'supervisor_id' => $doId,
            'winery_id' => $winery->id,
            'assigned_by' => $doId,
        ]);

        $this->showLinkModal = false;
        $this->toastSuccess("{$winery->name} adscrita a la denominación correctamente.");
    }

    public function unlinkWinery(int $wineryId): void
    {
        $doId = Auth::id();

        $relation = SupervisorWinery::where('supervisor_id', $doId)
            ->where('winery_id', $wineryId)
            ->firstOrFail();

        $winery = User::find($wineryId);
        $relation->delete();

        $this->toastSuccess(__(':name desvinculada de la denominación.', ['name' => $winery->name ?? __('La bodega')]));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

        // Available vintages for filter
        $availableVintages = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->select('vintage')
            ->distinct()
            ->orderByDesc('vintage')
            ->pluck('vintage');

        $vintage = $this->vintageFilter ?: ($availableVintages->first() ?? now()->year);

        // Harvest totals per winery for selected vintage
        $harvestStats = DB::table('harvests')
            ->whereIn('winery_id', $wineryIds)
            ->where('vintage', $vintage)
            ->select('winery_id', DB::raw('SUM(total_weight) as total_kg'), DB::raw('COUNT(*) as reception_count'))
            ->groupBy('winery_id')
            ->get()
            ->keyBy('winery_id');

        // Viticulturist count per winery via this DO
        $vitCountByWinery = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->select('winery_id', DB::raw('count(distinct viticulturist_id) as vit_count'))
            ->groupBy('winery_id')
            ->pluck('vit_count', 'winery_id');

        $query = User::whereIn('id', $wineryIds);

        if ($this->search) {
            $search = '%'.strtolower($this->search).'%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$search]);
            });
        }

        $wineries = $query->orderBy('name')->paginate(15);

        // Candidates for linking: winery/producer users not already under this DO
        $linkCandidates = collect();
        if ($this->showLinkModal && strlen($this->linkSearch) >= 2) {
            $term = '%'.mb_strtolower($this->linkSearch).'%';
            $linkCandidates = User::whereIn('role', [User::ROLE_WINERY, User::ROLE_PRODUCER])
                ->whereNotIn('id', $wineryIds)
                ->where(function ($q) use ($term) {
                    $q->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                })
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'email']);
        }

        return view('livewire.supervisor.oversight.wineries.index', [
            'wineries' => $wineries,
            'harvestStats' => $harvestStats,
            'vitCountByWinery' => $vitCountByWinery,
            'availableVintages' => $availableVintages,
            'vintage' => $vintage,
            'totalWineries' => $wineryIds->count(),
            'linkCandidates' => $linkCandidates,
        ]);
    }
}
