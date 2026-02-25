<?php

namespace App\Livewire\Viticulturist\PlotEnvironments;

use App\Models\Campaign;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\PlotEnvironment;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithToastNotifications;

    public $filterCampaign = '';

    // Form
    public bool $showModal = false;
    public ?int $editingId = null;

    public $campaign_id = '';
    public $plot_id = '';
    public $plot_planting_id = '';
    public $water_intake_nearby = false;
    public $water_intake_distance_m = '';
    public $protected_zone_total = false;
    public $protected_zone_partial = false;
    public $protection_zone_type = '';
    public $buffer_zone_m = '';
    public $slope_pct = '';
    public $erosion_risk = false;
    public $notes = '';

    public function mount()
    {
        $user = Auth::user();
        $campaign = Campaign::getOrCreateActiveForYear($user->id);
        $this->filterCampaign = $campaign?->id ?? '';
        $this->campaign_id = $campaign?->id ?? '';
    }

    public function openCreate()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id)
    {
        $entry = PlotEnvironment::where('viticulturist_id', Auth::id())->findOrFail($id);
        $this->editingId = $id;
        $this->campaign_id = $entry->campaign_id;
        $this->plot_id = $entry->plot_id;
        $this->plot_planting_id = $entry->plot_planting_id ?? '';
        $this->water_intake_nearby = $entry->water_intake_nearby;
        $this->water_intake_distance_m = $entry->water_intake_distance_m ?? '';
        $this->protected_zone_total = $entry->protected_zone_total;
        $this->protected_zone_partial = $entry->protected_zone_partial;
        $this->protection_zone_type = $entry->protection_zone_type ?? '';
        $this->buffer_zone_m = $entry->buffer_zone_m ?? '';
        $this->slope_pct = $entry->slope_pct ?? '';
        $this->erosion_risk = $entry->erosion_risk;
        $this->notes = $entry->notes ?? '';
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate($this->rules());
        $user = Auth::user();

        $data = [
            'campaign_id'             => $this->campaign_id,
            'plot_id'                 => $this->plot_id,
            'plot_planting_id'        => $this->plot_planting_id ?: null,
            'viticulturist_id'        => $user->id,
            'water_intake_nearby'     => $this->water_intake_nearby,
            'water_intake_distance_m' => $this->water_intake_distance_m ?: null,
            'protected_zone_total'    => $this->protected_zone_total,
            'protected_zone_partial'  => $this->protected_zone_partial,
            'protection_zone_type'    => $this->protection_zone_type ?: null,
            'buffer_zone_m'           => $this->buffer_zone_m ?: null,
            'slope_pct'               => $this->slope_pct ?: null,
            'erosion_risk'            => $this->erosion_risk,
            'notes'                   => $this->notes ?: null,
        ];

        if ($this->editingId) {
            PlotEnvironment::where('viticulturist_id', $user->id)->findOrFail($this->editingId)->update($data);
            $this->toastSuccess('Entorno de parcela actualizado.');
        } else {
            PlotEnvironment::updateOrCreate(
                ['campaign_id' => $this->campaign_id, 'plot_id' => $this->plot_id],
                $data
            );
            $this->toastSuccess('Entorno de parcela registrado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id)
    {
        PlotEnvironment::where('viticulturist_id', Auth::id())->findOrFail($id)->delete();
        $this->toastSuccess('Registro eliminado.');
    }

    protected function rules(): array
    {
        return [
            'campaign_id'             => 'required|exists:campaigns,id',
            'plot_id'                 => 'required|exists:plots,id',
            'plot_planting_id'        => 'nullable|exists:plot_plantings,id',
            'water_intake_nearby'     => 'boolean',
            'water_intake_distance_m' => 'nullable|numeric|min:0',
            'protected_zone_total'    => 'boolean',
            'protected_zone_partial'  => 'boolean',
            'protection_zone_type'    => 'nullable|string|max:100',
            'buffer_zone_m'           => 'nullable|numeric|min:0',
            'slope_pct'               => 'nullable|numeric|min:0|max:100',
            'erosion_risk'            => 'boolean',
            'notes'                   => 'nullable|string',
        ];
    }

    protected function resetForm()
    {
        $this->editingId = null;
        $this->plot_id = '';
        $this->plot_planting_id = '';
        $this->water_intake_nearby = false;
        $this->water_intake_distance_m = '';
        $this->protected_zone_total = false;
        $this->protected_zone_partial = false;
        $this->protection_zone_type = '';
        $this->buffer_zone_m = '';
        $this->slope_pct = '';
        $this->erosion_risk = false;
        $this->notes = '';
        $this->resetValidation();
    }

    public function render()
    {
        $user = Auth::user();

        $query = PlotEnvironment::with(['plot', 'plotPlanting.grape'])
            ->where('viticulturist_id', $user->id);

        if ($this->filterCampaign) {
            $query->where('campaign_id', $this->filterCampaign);
        }

        $entries = $query->orderBy('plot_id')->paginate(20);
        $campaigns = Campaign::forViticulturist($user->id)->orderByDesc('year')->get();
        $plots = Plot::where('viticulturist_id', $user->id)->active()->get();
        $plantings = PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $user->id))
            ->with(['plot', 'grape'])->active()->get();

        return view('livewire.viticulturist.plot-environments.index', [
            'entries'   => $entries,
            'campaigns' => $campaigns,
            'plots'     => $plots,
            'plantings' => $plantings,
        ]);
    }
}
