<?php

namespace App\Livewire\Supervisor\Growers;

use App\Livewire\Concerns\WithGrowerCreate;
use App\Livewire\Concerns\WithGrowerInvitation;
use App\Livewire\Concerns\WithGrowerLink;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\NotebookAccessRequest;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithGrowerCreate, WithGrowerInvitation, WithGrowerLink, WithPagination, WithToastNotifications;

    public string $search = '';

    public string $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
        $this->resetPage();
    }

    public function removeGrower(int $growerId): void
    {
        $doId = Auth::id();

        $relation = SupervisorViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->firstOrFail();

        $grower = User::find($growerId);

        WineryViticulturist::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->delete();

        NotebookAccessRequest::where('supervisor_id', $doId)
            ->where('viticulturist_id', $growerId)
            ->delete();

        $relation->delete();

        $this->toastSuccess(__(':name eliminado del pool de la denominación.', ['name' => $grower->name ?? __('El viticultor')]));
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $poolIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        $wineryNamesByVit = WineryViticulturist::where('supervisor_id', $doId)
            ->where('source', WineryViticulturist::SOURCE_SUPERVISOR)
            ->whereIn('viticulturist_id', $poolIds)
            ->with(['winery:id,name'])
            ->get()
            ->groupBy('viticulturist_id')
            ->map(fn ($rows) => $rows->pluck('winery.name')->filter()->unique()->implode(', '));

        $plotStatsByVit = DB::table('plots')
            ->whereIn('viticulturist_id', $poolIds)
            ->where('active', true)
            ->select(
                'viticulturist_id',
                DB::raw('COUNT(*) as plot_count'),
                DB::raw('COALESCE(SUM(area), 0) as total_area')
            )
            ->groupBy('viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $activePlantingsByVit = DB::table('plot_plantings')
            ->join('plots', 'plots.id', '=', 'plot_plantings.plot_id')
            ->whereIn('plots.viticulturist_id', $poolIds)
            ->where('plot_plantings.status', 'active')
            ->select(
                'plots.viticulturist_id',
                DB::raw('COUNT(*) as planting_count'),
            )
            ->groupBy('plots.viticulturist_id')
            ->get()
            ->keyBy('viticulturist_id');

        $allGrowers = User::whereIn('id', $poolIds)->get(['id', 'can_login', 'invitation_token', 'invitation_expires_at']);

        $countsByStatus = [
            'all' => $allGrowers->count(),
            'ghost' => $allGrowers->filter(fn ($u) => ! $u->can_login && (! $u->invitation_token || ($u->invitation_expires_at && $u->invitation_expires_at->isPast())))->count(),
            'invited' => $allGrowers->filter(fn ($u) => ! $u->can_login && $u->invitation_token && $u->invitation_expires_at?->isFuture())->count(),
            'active' => $allGrowers->filter(fn ($u) => $u->can_login)->count(),
        ];

        $query = User::whereIn('id', $poolIds);

        match ($this->statusFilter) {
            'ghost' => $query->where('can_login', false)->where(function ($q) {
                $q->whereNull('invitation_token')
                    ->orWhere('invitation_expires_at', '<=', now());
            }),
            'invited' => $query->where('can_login', false)
                ->whereNotNull('invitation_token')
                ->where('invitation_expires_at', '>', now()),
            'active' => $query->where('can_login', true),
            default => null,
        };

        if ($this->search) {
            $term = '%'.mb_strtolower($this->search).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        $growers = $query->orderBy('name')->paginate(15);
        $totalGrowerCount = $poolIds->count();

        $ghostGrowerForModal = $this->showInviteModal && $this->inviteGrowerId
            ? User::find($this->inviteGrowerId)
            : null;

        $linkCandidates = $this->showLinkModal ? $this->searchLinkCandidates() : [];

        $linkSelectedUser = $this->linkSelectedId
            ? collect($linkCandidates)->firstWhere('id', $this->linkSelectedId)
            : null;

        return view('livewire.supervisor.growers.index', [
            'growers' => $growers,
            'plotStatsByVit' => $plotStatsByVit,
            'activePlantingsByVit' => $activePlantingsByVit,
            'wineryNamesByVit' => $wineryNamesByVit,
            'totalGrowerCount' => $totalGrowerCount,
            'countsByStatus' => $countsByStatus,
            'ghostGrowerForModal' => $ghostGrowerForModal,
            'linkCandidates' => $linkCandidates,
            'linkSelectedUser' => $linkSelectedUser,
        ]);
    }
}
