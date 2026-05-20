<?php

namespace App\Livewire\Admin\SecurityLog;

use App\Models\SecurityEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterDateFrom = '';
    public string $filterDateTo   = '';
    public string $filterLevel    = '';
    public string $filterEvent    = '';
    public string $search         = '';
    public bool   $showInternal   = false;

    protected $queryString = [
        'filterDateFrom' => ['except' => '', 'as' => 'from'],
        'filterDateTo'   => ['except' => '', 'as' => 'to'],
        'filterLevel'    => ['except' => ''],
        'filterEvent'    => ['except' => ''],
        'search'         => ['except' => ''],
        'showInternal'   => ['except' => false, 'as' => 'internal'],
    ];

    public function mount(): void
    {
        $this->filterDateFrom = now()->subDays(6)->format('Y-m-d');
        $this->filterDateTo   = now()->format('Y-m-d');
    }

    public function updatingSearch()         { $this->resetPage(); }
    public function updatingFilterDateFrom() { $this->resetPage(); }
    public function updatingFilterDateTo()   { $this->resetPage(); }
    public function updatingFilterLevel()    { $this->resetPage(); }
    public function updatingFilterEvent()    { $this->resetPage(); }
    public function updatingShowInternal()   { $this->resetPage(); }

    public function toggleInternal(): void
    {
        $this->showInternal = !$this->showInternal;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->filterDateFrom = now()->subDays(6)->format('Y-m-d');
        $this->filterDateTo   = now()->format('Y-m-d');
        $this->filterLevel    = '';
        $this->filterEvent    = '';
        $this->search         = '';
        $this->showInternal   = false;
        $this->resetPage();
    }

    private function baseQuery()
    {
        $query = SecurityEvent::query();

        if (!$this->showInternal) {
            foreach (User::INTERNAL_EMAIL_PATTERNS as $pattern) {
                $query->where(function ($q) use ($pattern) {
                    $q->whereNull('email')
                      ->orWhere('email', 'not like', "%{$pattern}%");
                });
            }
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }
        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }
        if ($this->filterLevel) {
            $query->where('level', $this->filterLevel);
        }
        if ($this->filterEvent) {
            $query->where('event', 'like', "%{$this->filterEvent}%");
        }
        if ($this->search) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('email',   'like', "%{$s}%")
                  ->orWhere('ip',    'like', "%{$s}%")
                  ->orWhere('event', 'like', "%{$s}%")
                  ->orWhere('message', 'like', "%{$s}%");
            });
        }

        return $query;
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $entries = $this->baseQuery()
            ->orderByDesc('created_at')
            ->paginate(50);

        $statsRaw = $this->baseQuery()
            ->selectRaw("COUNT(*) as total")
            ->selectRaw("SUM(CASE WHEN level = 'info' THEN 1 ELSE 0 END) as info")
            ->selectRaw("SUM(CASE WHEN level IN ('warning','notice') THEN 1 ELSE 0 END) as warnings")
            ->selectRaw("SUM(CASE WHEN level IN ('alert','error','critical','emergency') THEN 1 ELSE 0 END) as alerts")
            ->first();

        $stats = [
            'total'    => (int) ($statsRaw->total    ?? 0),
            'info'     => (int) ($statsRaw->info     ?? 0),
            'warnings' => (int) ($statsRaw->warnings ?? 0),
            'alerts'   => (int) ($statsRaw->alerts   ?? 0),
        ];

        return view('livewire.admin.security-log.index', [
            'entries' => $entries,
            'stats'   => $stats,
        ])->layout('layouts.app', [
            'title'       => 'Log de Seguridad - Admin - Agro365',
            'description' => 'Historial de eventos de seguridad del sistema',
        ]);
    }
}
