<?php

namespace App\Livewire\Viticulturist\ResidueAnalyses;

use App\Models\Campaign;
use App\Models\PlotPlanting;
use App\Models\ResidueAnalysis;
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
    public $filterCompliant = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $plot_planting_id = '';
    public $analysis_date = '';
    public $sample_date = '';
    public $laboratory_name = '';
    public $laboratory_accreditation = '';
    public $sample_type = '';
    public $overall_compliant = true;
    public $certificate_file = '';
    public $notes = '';
    public $results = [];

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->campaign_id = $campaign?->id ?? '';
        $this->analysis_date = now()->format('Y-m-d');
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = ResidueAnalysis::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id;
        $this->plot_planting_id = $entry->plot_planting_id ?? '';
        $this->analysis_date = $entry->analysis_date->format('Y-m-d');
        $this->sample_date = $entry->sample_date?->format('Y-m-d') ?? '';
        $this->laboratory_name = $entry->laboratory_name;
        $this->laboratory_accreditation = $entry->laboratory_accreditation ?? '';
        $this->sample_type = $entry->sample_type ?? '';
        $this->overall_compliant = $entry->overall_compliant;
        $this->certificate_file = $entry->certificate_file ?? '';
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'campaign_id'              => $this->campaign_id,
            'plot_planting_id'         => $this->plot_planting_id ?: null,
            'viticulturist_id'         => $user->id,
            'analysis_date'            => $this->analysis_date,
            'sample_date'              => $this->sample_date ?: null,
            'laboratory_name'          => $this->laboratory_name,
            'laboratory_accreditation' => $this->laboratory_accreditation ?: null,
            'sample_type'              => $this->sample_type ?: null,
            'overall_compliant'        => $this->overall_compliant,
            'certificate_file'         => $this->certificate_file ?: null,
            'notes'                    => $this->notes ?: null,
            'active'                   => true,
        ];

        if ($this->editingId) {
            ResidueAnalysis::where('viticulturist_id', $user->id)
                ->findOrFail($this->editingId)
                ->update($data);
            $this->toastSuccess('Análisis actualizado correctamente.');
        } else {
            ResidueAnalysis::create($data);
            $this->toastSuccess('Análisis registrado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function deactivate(int $id)
    {
        ResidueAnalysis::where('viticulturist_id', Auth::id())
            ->findOrFail($id)
            ->update(['active' => false]);
        $this->toastSuccess('Análisis archivado.');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'              => 'required|exists:campaigns,id',
            'plot_planting_id'         => 'nullable|exists:plot_plantings,id',
            'analysis_date'            => 'required|date',
            'sample_date'              => 'nullable|date',
            'laboratory_name'          => 'required|string|max:255',
            'laboratory_accreditation' => 'nullable|string|max:50',
            'sample_type'              => 'nullable|string|max:50',
            'overall_compliant'        => 'boolean',
            'certificate_file'         => 'nullable|string|max:500',
            'notes'                    => 'nullable|string',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->plot_planting_id = '';
        $this->analysis_date = now()->format('Y-m-d');
        $this->sample_date = '';
        $this->laboratory_name = '';
        $this->laboratory_accreditation = '';
        $this->sample_type = '';
        $this->overall_compliant = true;
        $this->certificate_file = '';
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = ResidueAnalysis::with(['plotPlanting.plot', 'plotPlanting.grape'])
            ->where('viticulturist_id', $user->id)
            ->active();

        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }
        if ($this->filterCompliant !== '') {
            $query->where('overall_compliant', (bool) $this->filterCompliant);
        }

        $entries = $query->orderByDesc('analysis_date')->paginate(15);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $plantings = PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $user->id))
            ->with(['plot', 'grape'])
            ->active()
            ->get();

        return view('livewire.viticulturist.residue-analyses.index', [
            'entries'   => $entries,
            'campaigns' => $campaigns,
            'plantings' => $plantings,
        ]);
    }
}
