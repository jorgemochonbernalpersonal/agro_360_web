<?php

namespace App\Livewire\Viticulturist\Phenology;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Concerns\WithViticulturistValidation;
use App\Models\Campaign;
use App\Models\PhenologyObservation;
use App\Models\PlotPlanting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications, WithViticulturistValidation;

    public PhenologyObservation $observation;

    public $plot_planting_id = '';
    public $campaign_id = '';
    public $event = '';
    public $obs_date = '';
    public $source = 'manual';
    public $confidence = 100;
    public $degree_days_accumulated = '';
    public $bbch_code = '';
    public $notes = '';

    public function mount(PhenologyObservation $observation): void
    {
        $observation->load('plotPlanting.plot');

        if ($observation->plotPlanting->plot->viticulturist_id !== Auth::id()) {
            abort(403);
        }

        $this->observation             = $observation;
        $this->plot_planting_id        = $observation->plot_planting_id;
        $this->campaign_id             = $observation->campaign_id;
        $this->event                   = $observation->event;
        $this->obs_date                = $observation->obs_date->format('Y-m-d');
        $this->source                  = $observation->source;
        $this->confidence              = $observation->confidence;
        $this->degree_days_accumulated = $observation->degree_days_accumulated ?? '';
        $this->bbch_code               = $observation->bbch_code ?? '';
        $this->notes                   = $observation->notes ?? '';
    }

    public function updatedEvent($value): void
    {
        $this->bbch_code = PhenologyObservation::BBCH_CODES[$value] ?? '';
    }

    protected function rules(): array
    {
        return [
            'plot_planting_id'        => $this->plotPlantingOwnershipRule(true),
            'campaign_id'             => $this->campaignOwnershipRule(),
            'event'                   => 'required|in:' . implode(',', array_keys(PhenologyObservation::EVENTS)),
            'obs_date'                => 'required|date',
            'source'                  => 'required|in:manual,sensor,model,auto',
            'confidence'              => 'required|integer|min:0|max:100',
            'degree_days_accumulated' => 'nullable|numeric|min:0',
            'bbch_code'               => 'nullable|integer|min:0|max:99',
            'notes'                   => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->observation->update([
            'plot_planting_id'        => $this->plot_planting_id,
            'campaign_id'             => $this->campaign_id,
            'event'                   => $this->event,
            'obs_date'                => $this->obs_date,
            'source'                  => $this->source,
            'confidence'              => $this->confidence,
            'degree_days_accumulated' => $this->degree_days_accumulated ?: null,
            'bbch_code'               => $this->bbch_code ?: null,
            'notes'                   => $this->notes ?: null,
        ]);

        $this->toastSuccess(__('Observación fenológica actualizada correctamente.'));

        return $this->viticulturistRoleRedirect('phenology.index', ['filter_planting_id' => $this->plot_planting_id]);
    }

    public function render()
    {
        $user = Auth::user();

        $campaigns = Campaign::where('viticulturist_id', $user->id)
            ->orderBy('year', 'desc')->get();

        $plantings = PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $user->id))
            ->where('status', 'active')
            ->with(['plot', 'grapeVariety'])
            ->orderBy('plot_id')
            ->get();

        return view('livewire.viticulturist.phenology.edit', [
            'campaigns' => $campaigns,
            'plantings' => $plantings,
            'events'    => PhenologyObservation::eventOptions(),
            'sources'   => PhenologyObservation::sourceOptions(),
        ])->layout('layouts.app');
    }
}
