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

class Create extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications, WithViticulturistValidation;

    public $plot_planting_id = '';
    public $campaign_id = '';
    public $event = '';
    public $obs_date = '';
    public $source = 'manual';
    public $confidence = 100;
    public $degree_days_accumulated = '';
    public $bbch_code = '';
    public $notes = '';
    public bool $plantingLocked = false;

    public function mount(): void
    {
        $user = Auth::user();

        $campaign = Campaign::where('viticulturist_id', $user->id)->where('active', true)->first();
        if ($campaign) {
            $this->campaign_id = $campaign->id;
        }

        $this->obs_date = now()->format('Y-m-d');

        $planting_id = request()->integer('planting_id') ?: null;
        if ($planting_id) {
            $planting = PlotPlanting::whereHas('plot', fn($q) => $q->where('viticulturist_id', $user->id))
                ->find($planting_id);
            if ($planting) {
                $this->plot_planting_id = $planting->id;
                $this->plantingLocked = true;
            }
        }
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

    public function save()
    {
        $this->validate();

        $existing = PhenologyObservation::where('plot_planting_id', $this->plot_planting_id)
            ->where('campaign_id', $this->campaign_id)
            ->where('event', $this->event)
            ->exists();

        PhenologyObservation::updateOrCreate(
            [
                'plot_planting_id' => $this->plot_planting_id,
                'campaign_id'      => $this->campaign_id,
                'event'            => $this->event,
            ],
            [
                'viticulturist_id'        => Auth::id(),
                'obs_date'                => $this->obs_date,
                'source'                  => $this->source,
                'confidence'              => $this->confidence,
                'degree_days_accumulated' => $this->degree_days_accumulated ?: null,
                'bbch_code'               => $this->bbch_code ?: null,
                'notes'                   => $this->notes ?: null,
                'active'                  => true,
            ]
        );

        $this->toastSuccess(
            $existing
                ? 'Registro actualizado — ya existía un estadio para esta plantación y campaña.'
                : 'Observación fenológica guardada correctamente.'
        );

        return $this->viticulturistRoleRedirect('phenology.index', $this->plot_planting_id ? ['filter_planting_id' => $this->plot_planting_id] : []);
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

        return view('livewire.viticulturist.phenology.create', [
            'campaigns' => $campaigns,
            'plantings' => $plantings,
            'events'    => PhenologyObservation::EVENTS,
            'sources'   => PhenologyObservation::SOURCES,
        ])->layout('layouts.app');
    }
}
