<?php

namespace App\Livewire\Supervisor\Oversight\Certifications;

use App\Models\Certification;
use App\Models\SupervisorViticulturist;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $filterVit    = '';
    public string $filterType   = '';
    public string $filterStatus = ''; // 'active', 'expiring', 'expired'

    protected $queryString = [
        'filterVit'    => ['except' => ''],
        'filterType'   => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatingFilterVit(): void    { $this->resetPage(); }
    public function updatingFilterType(): void   { $this->resetPage(); }
    public function updatingFilterStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->filterVit    = '';
        $this->filterType   = '';
        $this->filterStatus = '';
        $this->resetPage();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $supervisorId = Auth::id();

        $viticulturistIds = SupervisorViticulturist::where('supervisor_id', $supervisorId)
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = Certification::whereIn('viticulturist_id', $viticulturistIds)
            ->with('viticulturist:id,name')
            ->orderBy('expiry_date');

        if ($this->filterVit) {
            $query->where('viticulturist_id', $this->filterVit);
        }

        if ($this->filterType) {
            $query->where('certification_type', $this->filterType);
        }

        if ($this->filterStatus === 'expired') {
            $query->where('active', true)->whereDate('expiry_date', '<', now());
        } elseif ($this->filterStatus === 'expiring') {
            $query->where('active', true)
                  ->whereDate('expiry_date', '>=', now())
                  ->whereDate('expiry_date', '<=', now()->addDays(60));
        } elseif ($this->filterStatus === 'active') {
            $query->where('active', true)->where(fn($q) =>
                $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', now())
            );
        }

        $certifications = $query->paginate(20);

        // Resumen
        $allCerts = Certification::whereIn('viticulturist_id', $viticulturistIds)->where('active', true)->get();
        $totalActive   = $allCerts->filter(fn($c) => !$c->is_expired)->count();
        $totalExpiring = $allCerts->filter(fn($c) => $c->is_expiring_soon)->count();
        $totalExpired  = $allCerts->filter(fn($c) => $c->is_expired)->count();

        $certTypes = Certification::CERTIFICATION_TYPES;

        return view('livewire.supervisor.oversight.certifications.index', [
            'certifications' => $certifications,
            'viticulturists' => $viticulturists,
            'certTypes'      => $certTypes,
            'totalActive'    => $totalActive,
            'totalExpiring'  => $totalExpiring,
            'totalExpired'   => $totalExpired,
        ]);
    }
}
