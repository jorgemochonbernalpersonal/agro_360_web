<?php

namespace App\Livewire\Viticulturist\ResidueManagements;

use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\ResidueManagement;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    // Filters
    public $filterCampaign = '';
    public $filterPractice = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $plot_id = '';
    public $plot_planting_id = '';
    public $date = '';
    public $practice_type = '';
    public $material_type = '';
    public $estimated_quantity = '';
    public $quantity_unit = 'kg';
    public $justification = '';
    public $notes = '';

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->campaign_id = $campaign?->id ?? '';
        $this->date = now()->format('Y-m-d');
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = ResidueManagement::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id;
        $this->plot_id = $entry->plot_id ?? '';
        $this->plot_planting_id = $entry->plot_planting_id ?? '';
        $this->date = $entry->date->format('Y-m-d');
        $this->practice_type = $entry->practice_type;
        $this->material_type = $entry->material_type;
        $this->estimated_quantity = $entry->estimated_quantity ?? '';
        $this->quantity_unit = $entry->quantity_unit ?? 'kg';
        $this->justification = $entry->justification ?? '';
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'campaign_id'        => $this->campaign_id,
            'plot_id'            => $this->plot_id ?: null,
            'plot_planting_id'   => $this->plot_planting_id ?: null,
            'viticulturist_id'   => $user->id,
            'date'               => $this->date,
            'practice_type'      => $this->practice_type,
            'material_type'      => $this->material_type,
            'estimated_quantity' => $this->estimated_quantity ?: null,
            'quantity_unit'      => $this->quantity_unit ?: null,
            'justification'      => $this->justification ?: null,
            'notes'              => $this->notes ?: null,
            'active'             => true,
        ];

        if ($this->editingId) {
            ResidueManagement::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Gestión de residuos actualizada.');
        } else {
            ResidueManagement::create($data);
            $this->toastSuccess('Gestión de residuos registrada.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deactivate(int $id)
    {
        ResidueManagement::where('viticulturist_id', Auth::id())
            ->findOrFail($id)
            ->update(['active' => false]);
        $this->toastSuccess('Registro archivado.');
    }

    protected function rules(): array
    {
        $rules = [
            'campaign_id'        => 'required|exists:campaigns,id',
            'plot_id'            => 'nullable|exists:plots,id',
            'plot_planting_id'   => 'nullable|exists:plot_plantings,id',
            'date'               => 'required|date',
            'practice_type'      => 'required|in:' . implode(',', array_keys(ResidueManagement::PRACTICE_TYPES)),
            'material_type'      => 'required|in:' . implode(',', array_keys(ResidueManagement::MATERIAL_TYPES)),
            'estimated_quantity' => 'nullable|numeric|min:0',
            'quantity_unit'      => 'nullable|in:kg,t',
            'justification'      => 'nullable|string',
            'notes'              => 'nullable|string',
        ];

        if ($this->practice_type === 'burning') {
            $rules['justification'] = 'required|string|min:20';
        }

        return $rules;
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->date = now()->format('Y-m-d');
        $this->practice_type = '';
        $this->material_type = '';
        $this->estimated_quantity = '';
        $this->quantity_unit = 'kg';
        $this->justification = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = ResidueManagement::with(['plot', 'plotPlanting.grape'])
            ->where('viticulturist_id', $user->id)
            ->active();

        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterPractice) {
            $query->where('practice_type', $this->filterPractice);
        }

        $entries = $query->orderByDesc('date')->paginate(15);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $plots = Plot::where('viticulturist_id', $user->id)->active()->get();
        $plantings = PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $user->id))
            ->with(['plot', 'grape'])
            ->active()
            ->get();

        return view('livewire.viticulturist.residue-managements.index', [
            'entries'       => $entries,
            'campaigns'     => $campaigns,
            'plots'         => $plots,
            'plantings'     => $plantings,
            'practiceTypes' => ResidueManagement::PRACTICE_TYPES,
            'materialTypes' => ResidueManagement::MATERIAL_TYPES,
        ]);
    }
}
