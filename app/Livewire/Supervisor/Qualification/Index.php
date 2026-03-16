<?php

namespace App\Livewire\Supervisor\Qualification;

use App\Models\DoQualification;
use App\Models\SupervisorWinery;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications;

    public string $currentTab    = 'all';
    public string $vintageFilter = '';
    public string $colorFilter   = '';
    public bool   $showCreate    = false;

    // Create form
    public string $winery_id          = '';
    public string $vintage            = '';
    public string $wine_name          = '';
    public string $color              = '';
    public string $alcohol_percentage = '';
    public string $qualification_date = '';
    public string $tasting_notes      = '';

    protected $queryString = [
        'currentTab'    => ['except' => 'all', 'as' => 'tab'],
        'vintageFilter' => ['except' => ''],
        'colorFilter'   => ['except' => ''],
    ];

    public function mount(): void
    {
        $this->vintage            = (string) now()->year;
        $this->qualification_date = now()->format('Y-m-d');
    }

    public function switchTab(string $tab): void
    {
        $this->currentTab = $tab;
        $this->resetPage();
    }

    public function toggleCreate(): void
    {
        $this->showCreate = !$this->showCreate;
    }

    public function saveQualification(): void
    {
        $this->validate([
            'winery_id'          => 'required|integer|exists:users,id',
            'vintage'            => 'required|integer|min:1990|max:2100',
            'wine_name'          => 'required|string|max:200',
            'color'              => 'nullable|in:tinto,blanco,rosado,espumoso,dulce,otro',
            'alcohol_percentage' => 'nullable|numeric|min:0|max:25',
            'qualification_date' => 'required|date',
            'tasting_notes'      => 'nullable|string',
        ]);

        $doId = Auth::id();

        $isLinked = SupervisorWinery::where('supervisor_id', $doId)
            ->where('winery_id', $this->winery_id)
            ->exists();

        if (!$isLinked) {
            $this->toastError('La bodega seleccionada no pertenece a esta denominación.');
            return;
        }

        DoQualification::create([
            'supervisor_id'      => $doId,
            'winery_id'          => $this->winery_id,
            'vintage'            => $this->vintage,
            'wine_name'          => $this->wine_name,
            'color'              => $this->color ?: null,
            'alcohol_percentage' => $this->alcohol_percentage ?: null,
            'qualification_date' => $this->qualification_date,
            'tasting_notes'      => $this->tasting_notes ?: null,
        ]);

        $this->reset(['wine_name', 'color', 'alcohol_percentage', 'tasting_notes']);
        $this->showCreate = false;
        $this->toastSuccess('Calificación registrada.');
    }

    public function qualify(int $id): void
    {
        DoQualification::forSupervisor(Auth::id())->findOrFail($id)
            ->update(['result' => DoQualification::RESULT_QUALIFIED, 'qualified_by' => Auth::id()]);
        $this->toastSuccess('Vino calificado DO.');
    }

    public function disqualify(int $id): void
    {
        DoQualification::forSupervisor(Auth::id())->findOrFail($id)
            ->update(['result' => DoQualification::RESULT_DISQUALIFIED, 'qualified_by' => Auth::id()]);
        $this->toastSuccess('Vino descalificado.');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $doId = Auth::id();

        $query = DoQualification::forSupervisor($doId)->with(['winery:id,name']);

        if ($this->currentTab !== 'all') {
            $query->where('result', $this->currentTab);
        }
        if ($this->vintageFilter) {
            $query->where('vintage', $this->vintageFilter);
        }
        if ($this->colorFilter) {
            $query->where('color', $this->colorFilter);
        }

        $qualifications = $query->orderByDesc('qualification_date')->paginate(15);

        $counts = [
            'all'          => DoQualification::forSupervisor($doId)->count(),
            'pending'      => DoQualification::forSupervisor($doId)->where('result', 'pending')->count(),
            'qualified'    => DoQualification::forSupervisor($doId)->where('result', 'qualified')->count(),
            'disqualified' => DoQualification::forSupervisor($doId)->where('result', 'disqualified')->count(),
        ];

        $availableVintages = DoQualification::forSupervisor($doId)
            ->select('vintage')->distinct()->orderByDesc('vintage')->pluck('vintage');

        $wineries = \App\Models\User::whereIn('id',
            SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id')
        )->orderBy('name')->get(['id', 'name']);

        $tabs = [
            'all'          => ['label' => 'Todas',          'count' => $counts['all']],
            'pending'      => ['label' => 'Pendientes',     'count' => $counts['pending']],
            'qualified'    => ['label' => 'Calificados',    'count' => $counts['qualified']],
            'disqualified' => ['label' => 'Descalificados', 'count' => $counts['disqualified']],
        ];

        return view('livewire.supervisor.qualification.index', [
            'qualifications'   => $qualifications,
            'tabs'             => $tabs,
            'wineries'         => $wineries,
            'availableVintages'=> $availableVintages,
            'colorLabels'      => DoQualification::COLOR_LABELS,
            'resultLabels'     => DoQualification::RESULT_LABELS,
            'resultColors'     => DoQualification::RESULT_COLORS,
        ]);
    }
}
