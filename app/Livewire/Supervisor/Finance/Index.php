<?php

namespace App\Livewire\Supervisor\Finance;

use App\Models\SupervisorViticulturist;
use App\Models\SupervisorWinery;
use App\Models\Subscription;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $activeTab = 'subscriptions';

    protected $queryString = [
        'activeTab' => ['except' => 'subscriptions', 'as' => 'tab'],
    ];

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $doId)
            ->pluck('viticulturist_id');

        $wineryIds = SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id');

        $activeSubscriptions = Subscription::whereIn('user_id', $viticulturistIds)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereDate('ends_at', '>=', now())
            ->count();

        $monthlyRevenue = Subscription::whereIn('user_id', $viticulturistIds)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereDate('ends_at', '>=', now())
            ->where('plan_type', Subscription::PLAN_MONTHLY)
            ->sum('amount');

        $yearlyRevenue = Subscription::whereIn('user_id', $viticulturistIds)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereDate('ends_at', '>=', now())
            ->where('plan_type', Subscription::PLAN_YEARLY)
            ->sum('amount');

        $subscriptions = Subscription::whereIn('user_id', $viticulturistIds)
            ->with(['user:id,name,email'])
            ->orderByDesc('starts_at')
            ->paginate(15);

        $harvestValueByWinery = DB::table('harvests')
            ->join('users', 'users.id', '=', 'harvests.winery_id')
            ->whereIn('harvests.winery_id', $wineryIds)
            ->where('harvests.status', 'active')
            ->where('harvests.vintage', now()->year)
            ->select(
                'users.name as winery_name',
                DB::raw('COALESCE(SUM(harvests.total_value), 0) as total_value'),
                DB::raw('COALESCE(SUM(harvests.total_weight), 0) as total_kg')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_value')
            ->get();

        $tabs = [
            'subscriptions' => ['label' => 'Suscripciones viticultores', 'count' => $viticulturistIds->count()],
            'wineries'      => ['label' => 'Recepciones bodegas',        'count' => $wineryIds->count()],
        ];

        return view('livewire.supervisor.finance.index', [
            'tabs'                 => $tabs,
            'activeSubscriptions'  => $activeSubscriptions,
            'monthlyRevenue'       => $monthlyRevenue,
            'yearlyRevenue'        => $yearlyRevenue,
            'subscriptions'        => $subscriptions,
            'harvestValueByWinery' => $harvestValueByWinery,
            'currentYear'          => now()->year,
        ]);
    }
}
