<?php

namespace App\Livewire\Supervisor\Qualification;

use App\Models\DoQualification;
use App\Models\SupervisorWinery;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
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

    // Edit modal
    public bool   $showEdit             = false;
    public ?int   $editId               = null;
    public string $editWineName         = '';
    public string $editVintage          = '';
    public string $editColor            = '';
    public string $editAlcohol          = '';
    public string $editBrix             = '';
    public string $editAcidity          = '';
    public string $editPh               = '';
    public string $editVisualScore      = '';
    public string $editAromaScore       = '';
    public string $editTasteScore       = '';
    public string $editOverallScore     = '';
    public string $editTastingNotes     = '';
    public string $editQualificationDate = '';

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
        Gate::authorize('create', DoQualification::class);

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
            $this->toastError(__('La bodega seleccionada no pertenece a esta denominación.'));
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
        $this->toastSuccess(__('Calificación registrada.'));
    }

    public function openEdit(int $id): void
    {
        $q = DoQualification::forSupervisor(Auth::id())->findOrFail($id);

        $this->editId               = $id;
        $this->editWineName         = $q->wine_name;
        $this->editVintage          = (string) $q->vintage;
        $this->editColor            = $q->color ?? '';
        $this->editAlcohol          = $q->alcohol_percentage !== null ? (string) $q->alcohol_percentage : '';
        $this->editBrix             = $q->brix_degree       !== null ? (string) $q->brix_degree       : '';
        $this->editAcidity          = $q->acidity_level     !== null ? (string) $q->acidity_level     : '';
        $this->editPh               = $q->ph_level          !== null ? (string) $q->ph_level          : '';
        $this->editVisualScore      = $q->visual_score      !== null ? (string) $q->visual_score      : '';
        $this->editAromaScore       = $q->aroma_score       !== null ? (string) $q->aroma_score       : '';
        $this->editTasteScore       = $q->taste_score       !== null ? (string) $q->taste_score       : '';
        $this->editOverallScore     = $q->overall_score     !== null ? (string) $q->overall_score     : '';
        $this->editTastingNotes     = $q->tasting_notes ?? '';
        $this->editQualificationDate = $q->qualification_date?->format('Y-m-d') ?? '';
        $this->showEdit             = true;
        $this->resetValidation();
    }

    public function closeEdit(): void
    {
        $this->showEdit = false;
        $this->editId   = null;
        $this->resetValidation();
    }

    public function updateQualification(): void
    {
        $qualification = DoQualification::forSupervisor(Auth::id())->findOrFail($this->editId);
        Gate::authorize('update', $qualification);

        $this->validate([
            'editWineName'          => 'required|string|max:200',
            'editVintage'           => 'required|integer|min:1990|max:2100',
            'editColor'             => 'nullable|in:tinto,blanco,rosado,espumoso,dulce,otro',
            'editAlcohol'           => 'nullable|numeric|min:0|max:25',
            'editBrix'              => 'nullable|numeric|min:0|max:50',
            'editAcidity'           => 'nullable|numeric|min:0|max:30',
            'editPh'                => 'nullable|numeric|min:2|max:5',
            'editVisualScore'       => 'nullable|numeric|min:0|max:10',
            'editAromaScore'        => 'nullable|numeric|min:0|max:10',
            'editTasteScore'        => 'nullable|numeric|min:0|max:10',
            'editOverallScore'      => 'nullable|numeric|min:0|max:100',
            'editTastingNotes'      => 'nullable|string',
            'editQualificationDate' => 'required|date',
        ]);

        $qualification->update([
            'wine_name'          => $this->editWineName,
            'vintage'            => $this->editVintage,
            'color'              => $this->editColor ?: null,
            'alcohol_percentage' => $this->editAlcohol  ?: null,
            'brix_degree'        => $this->editBrix      ?: null,
            'acidity_level'      => $this->editAcidity   ?: null,
            'ph_level'           => $this->editPh        ?: null,
            'visual_score'       => $this->editVisualScore  ?: null,
            'aroma_score'        => $this->editAromaScore   ?: null,
            'taste_score'        => $this->editTasteScore   ?: null,
            'overall_score'      => $this->editOverallScore ?: null,
            'tasting_notes'      => $this->editTastingNotes ?: null,
            'qualification_date' => $this->editQualificationDate,
        ]);

        $this->closeEdit();
        $this->toastSuccess(__('Calificación actualizada.'));
    }

    public function qualify(int $id): void
    {
        $q = DoQualification::forSupervisor(Auth::id())->findOrFail($id);
        Gate::authorize('update', $q);
        $q->update(['result' => DoQualification::RESULT_QUALIFIED, 'qualified_by' => Auth::id()]);
        $this->toastSuccess(__('Vino calificado DO.'));
    }

    public function disqualify(int $id): void
    {
        $q = DoQualification::forSupervisor(Auth::id())->findOrFail($id);
        Gate::authorize('update', $q);
        $q->update(['result' => DoQualification::RESULT_DISQUALIFIED, 'qualified_by' => Auth::id()]);
        $this->toastSuccess(__('Vino descalificado.'));
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

        $resultCounts = DoQualification::forSupervisor($doId)
            ->selectRaw('result, count(*) as total')
            ->groupBy('result')
            ->pluck('total', 'result');

        $counts = [
            'all'          => $resultCounts->sum(),
            'pending'      => $resultCounts->get('pending', 0),
            'qualified'    => $resultCounts->get('qualified', 0),
            'disqualified' => $resultCounts->get('disqualified', 0),
        ];

        $availableVintages = DoQualification::forSupervisor($doId)
            ->select('vintage')->distinct()->orderByDesc('vintage')->pluck('vintage');

        $wineries = \App\Models\User::whereIn('id',
            SupervisorWinery::where('supervisor_id', $doId)->pluck('winery_id')
        )->orderBy('name')->get(['id', 'name']);

        $tabs = [
            'all'          => ['label' => __('Todas'),          'count' => $counts['all']],
            'pending'      => ['label' => __('Pendientes'),     'count' => $counts['pending']],
            'qualified'    => ['label' => __('Calificados'),    'count' => $counts['qualified']],
            'disqualified' => ['label' => __('Descalificados'), 'count' => $counts['disqualified']],
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
