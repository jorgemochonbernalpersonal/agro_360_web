<?php

namespace App\Livewire\Viticulturist\AgriInsurance;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriInsurance;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $filter_status = '';
    public string $filter_coverage_type = '';

    protected $queryString = [
        'filter_status'        => ['except' => '', 'as' => 'status'],
        'filter_coverage_type' => ['except' => '', 'as' => 'coverage'],
    ];

    public function updatingFilterStatus(): void { $this->resetPage(); }
    public function updatingFilterCoverageType(): void { $this->resetPage(); }

    public function delete(int $id): void
    {
        AgriInsurance::where('viticulturist_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess('Seguro eliminado.');
    }

    public function render()
    {
        $user = Auth::user();

        $query = AgriInsurance::where('viticulturist_id', $user->id);

        if ($this->filter_status) {
            $query->where('status', $this->filter_status);
        }
        if ($this->filter_coverage_type) {
            $query->where('coverage_type', $this->filter_coverage_type);
        }

        $insurances  = $query->orderByDesc('end_date')->paginate(20);
        $activeCount = AgriInsurance::where('viticulturist_id', $user->id)->active()->count();
        $expiringSoon = AgriInsurance::where('viticulturist_id', $user->id)
            ->active()
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->count();

        return view('livewire.viticulturist.agri-insurance.index', [
            'insurances'    => $insurances,
            'activeCount'   => $activeCount,
            'expiringSoon'  => $expiringSoon,
            'coverageTypes' => AgriInsurance::COVERAGE_TYPES,
            'statuses'      => AgriInsurance::STATUSES,
        ])->layout('layouts.app');
    }
}
