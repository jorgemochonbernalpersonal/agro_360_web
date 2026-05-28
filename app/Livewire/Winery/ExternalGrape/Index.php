<?php

namespace App\Livewire\Winery\ExternalGrape;

use App\Models\ExternalGrape;
use App\Models\GrapeVariety;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $search        = '';
    public string $typeFilter    = '';
    public string $statusFilter  = '';
    public string $vintageFilter = '';
    public string $colorFilter   = '';

    protected $queryString = [
        'search'        => ['except' => ''],
        'typeFilter'    => ['except' => ''],
        'statusFilter'  => ['except' => ''],
        'vintageFilter' => ['except' => ''],
        'colorFilter'   => ['except' => ''],
    ];

    public function updatingSearch(): void        { $this->resetPage(); }
    public function updatingTypeFilter(): void    { $this->resetPage(); }
    public function updatingStatusFilter(): void  { $this->resetPage(); }
    public function updatingVintageFilter(): void { $this->resetPage(); }
    public function updatingColorFilter(): void   { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search        = '';
        $this->typeFilter    = '';
        $this->statusFilter  = '';
        $this->vintageFilter = '';
        $this->colorFilter   = '';
        $this->resetPage();
    }

    public function archive(int $id): void
    {
        $grape = ExternalGrape::where('user_id', Auth::id())->findOrFail($id);
        $grape->update(['status' => 'archived']);
        $this->toastSuccess(__('Partida archivada.'));
    }

    public function render()
    {
        $wineryId = Auth::id();

        $query = ExternalGrape::with(['grapeVariety:id,name', 'container:id,name'])
            ->where('user_id', $wineryId)
            ->when($this->search,        fn($q) => $q->where('supplier_name', 'like', '%'.$this->search.'%'))
            ->when($this->typeFilter,    fn($q) => $q->where('grape_type', $this->typeFilter))
            ->when($this->statusFilter,  fn($q) => $q->where('status', $this->statusFilter))
            ->when($this->vintageFilter, fn($q) => $q->where('vintage_year', $this->vintageFilter))
            ->when($this->colorFilter,   fn($q) => $q->where('color', $this->colorFilter));

        $grapes = (clone $query)->orderByDesc('entry_date')->paginate(25);

        $statsRow = (clone $query)->where('status', 'available')
            ->selectRaw('COALESCE(SUM(total_weight_kg), 0) as total_kg, COALESCE(SUM(total_weight_kg - used_weight_kg), 0) as available_kg, COUNT(*) as partidas')
            ->first();
        $stats = [
            'total_kg'     => (float) $statsRow->total_kg,
            'available_kg' => (float) $statsRow->available_kg,
            'partidas'     => (int) $statsRow->partidas,
        ];

        $vintages = ExternalGrape::where('user_id', $wineryId)
            ->whereNotNull('vintage_year')
            ->orderByDesc('vintage_year')
            ->pluck('vintage_year')
            ->unique();

        return view('livewire.winery.external-grape.index', [
            'grapes'   => $grapes,
            'stats'    => $stats,
            'vintages' => $vintages,
            'types'    => ExternalGrape::typeOptions(),
            'colors'   => ExternalGrape::colorOptions(),
            'statuses' => ExternalGrape::statusOptions(),
        ])->layout('layouts.app');
    }
}
