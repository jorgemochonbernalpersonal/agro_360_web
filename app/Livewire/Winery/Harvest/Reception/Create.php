<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\Harvest;
use App\Models\Plot;
use App\Models\PlotPlanting;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications;

    // Step 1: Context
    public string $viticulturist_id = '';
    public string $plot_id          = '';
    public string $plot_planting_id = '';

    // Step 2: Reception data
    public int    $vintage_year               = 0;
    public string $harvest_start_date         = '';
    public string $harvest_ticket_number      = '';
    public string $total_weight               = '';
    public string $price_per_kg               = '';

    // Quality
    public string $baume_degree    = '';
    public string $brix_degree     = '';
    public string $acidity_level   = '';
    public string $ph_level        = '';

    // Sanitary state
    public string $health_status             = '';
    public string $sanitary_state_grapes     = '';
    public string $sanitary_state_agraces    = '';
    public string $sanitary_state_botrytis   = '';
    public string $sanitary_state_oidium     = '';
    public string $sanitary_state_mildew     = '';

    // Transport
    public string $transport_document_number = '';
    public string $destination_rega_code     = '';
    public string $vehicle_plate             = '';

    public string $notes = '';

    // Computed / dynamic
    public ?PlotPlanting $selectedPlanting    = null;
    public float         $totalHarvestedInCampaign = 0;
    public ?array        $harvestLimitInfo    = null;

    /** @var array<int, mixed> */
    public array $availablePlots     = [];
    /** @var array<int, mixed> */
    public array $availablePlantings = [];

    public function mount(): void
    {
        $this->harvest_start_date = now()->toDateString();
        $this->vintage_year       = now()->year;
    }

    public function updatedViticulturistId(): void
    {
        $this->plot_id          = '';
        $this->plot_planting_id = '';
        $this->availablePlots   = [];
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (!$this->viticulturist_id) return;

        $this->availablePlots = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->where('active', true)
            ->whereHas('plantings', fn($q) => $q->where('status', 'active'))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function updatedPlotId(): void
    {
        $this->plot_planting_id   = '';
        $this->availablePlantings = [];
        $this->resetPlantingState();

        if (!$this->plot_id) return;

        $this->availablePlantings = PlotPlanting::where('plot_id', $this->plot_id)
            ->where('status', 'active')
            ->with('grapeVariety:id,name')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'label'       => ($p->grapeVariety?->name ?? $p->name ?? 'Sin variedad')
                    . ($p->area_planted ? ' — ' . number_format($p->area_planted, 2) . ' ha' : ''),
                'area'        => $p->area_planted,
                'limit_kg'    => $p->harvest_limit_kg,
                'planting_year' => $p->planting_year,
            ])
            ->toArray();

        // Auto-select if only one planting
        if (count($this->availablePlantings) === 1) {
            $this->plot_planting_id = (string) $this->availablePlantings[0]['id'];
            $this->loadPlantingState();
        }
    }

    public function updatedPlotPlantingId(): void
    {
        $this->loadPlantingState();
    }

    public function updatedTotalWeight(): void
    {
        $this->updateLimitInfo();
    }

    public function updatedHarvestStartDate(): void
    {
        if ($this->selectedPlanting) {
            $this->totalHarvestedInCampaign = $this->selectedPlanting
                ->getTotalActualYieldForVintage($this->harvestVintage());
            $this->updateLimitInfo();
        }
    }

    public function updatedVintageYear(): void
    {
        if ($this->selectedPlanting) {
            $this->totalHarvestedInCampaign = $this->selectedPlanting
                ->getTotalActualYieldForVintage($this->harvestVintage());
            $this->updateLimitInfo();
        }
    }

    protected function loadPlantingState(): void
    {
        if (!$this->plot_planting_id) {
            $this->resetPlantingState();
            return;
        }

        $this->selectedPlanting = PlotPlanting::with('grapeVariety')->find($this->plot_planting_id);
        if (!$this->selectedPlanting) return;

        $vintage = $this->harvestVintage();
        $this->totalHarvestedInCampaign = $this->selectedPlanting
            ->getTotalActualYieldForVintage($vintage);

        $this->updateLimitInfo();
    }

    protected function updateLimitInfo(): void
    {
        if (!$this->selectedPlanting) {
            $this->harvestLimitInfo = null;
            return;
        }

        $vintage      = $this->harvestVintage();
        $effectiveLimit = $this->selectedPlanting->effectiveHarvestLimitKg($vintage);

        if ($effectiveLimit === null) {
            $this->harvestLimitInfo = null;
            return;
        }

        $harvested = $this->totalHarvestedInCampaign;
        $adding    = (float) ($this->total_weight ?: 0);
        $newTotal  = $harvested + $adding;
        $rawLimit  = (float) $this->selectedPlanting->harvest_limit_kg;

        $this->harvestLimitInfo = [
            'limit'          => $effectiveLimit,
            'raw_limit'      => $rawLimit,
            'age_factor'     => $rawLimit > 0 ? round($effectiveLimit / $rawLimit * 100) : 100,
            'harvested'      => $harvested,
            'adding'         => $adding,
            'new_total'      => $newTotal,
            'remaining'      => max(0, $effectiveLimit - $newTotal),
            'percentage'     => $effectiveLimit > 0 ? round(($newTotal / $effectiveLimit) * 100, 1) : 0,
            'exceeds'        => $newTotal > $effectiveLimit,
        ];
    }

    protected function harvestVintage(): int
    {
        return $this->vintage_year ?: now()->year;
    }

    protected function resetPlantingState(): void
    {
        $this->selectedPlanting         = null;
        $this->totalHarvestedInCampaign = 0;
        $this->harvestLimitInfo         = null;
    }

    protected function rules(): array
    {
        return [
            'viticulturist_id'           => ['required', 'exists:users,id'],
            'plot_id'                    => ['required', 'exists:plots,id'],
            'plot_planting_id'           => ['required', 'exists:plot_plantings,id'],
            'vintage_year'               => ['required', 'integer', 'min:2000', 'max:' . (now()->year + 1)],
            'harvest_start_date'         => ['required', 'date'],
            'harvest_ticket_number'      => ['nullable', 'string', 'max:50'],
            'total_weight'               => ['required', 'numeric', 'min:0.01'],
            'price_per_kg'               => ['nullable', 'numeric', 'min:0'],
            'baume_degree'               => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree'                => ['nullable', 'numeric', 'min:0', 'max:40'],
            'acidity_level'              => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level'                   => ['nullable', 'numeric', 'min:0', 'max:14'],
            'health_status'              => ['nullable', 'in:sano,daño_leve,daño_moderado,daño_grave'],
            'sanitary_state_grapes'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_agraces'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_botrytis'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_oidium'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_mildew'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transport_document_number'  => ['nullable', 'string', 'max:50'],
            'destination_rega_code'      => ['nullable', 'string', 'max:20'],
            'vehicle_plate'              => ['nullable', 'string', 'max:20'],
            'notes'                      => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'viticulturist_id.required'  => 'Selecciona un viticultor.',
            'vintage_year.required'      => 'La añada es obligatoria.',
            'plot_id.required'           => 'Selecciona una parcela.',
            'plot_planting_id.required'  => 'Selecciona una plantación.',
            'harvest_start_date.required'=> 'La fecha de recepción es obligatoria.',
            'total_weight.required'      => 'El peso recibido es obligatorio.',
            'total_weight.min'           => 'El peso debe ser mayor que 0.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $wineryId = Auth::id();

        // Guard: viticulturist must be linked to this winery
        $linked = WineryViticulturist::where('winery_id', $wineryId)
            ->where('viticulturist_id', $this->viticulturist_id)
            ->exists();

        if (!$linked) {
            $this->toastError('El viticultor no está vinculado a tu bodega.');
            return;
        }

        // Auto-create/get campaign for the vintage year
        $campaign = Campaign::getOrCreateActiveForYear($wineryId, $this->vintage_year);

        if (!$campaign) {
            $this->toastError('No se pudo obtener la campaña. Inténtalo de nuevo.');
            return;
        }

        // Guard: plot must belong to the viticulturist
        $plot = Plot::where('viticulturist_id', $this->viticulturist_id)
            ->findOrFail($this->plot_id);

        // Guard: planting must belong to the plot
        $planting = PlotPlanting::where('plot_id', $plot->id)
            ->findOrFail($this->plot_planting_id);

        try {
            DB::transaction(function () use ($wineryId, $campaign, $plot, $planting) {
                $activity = AgriculturalActivity::create([
                    'plot_id'          => $plot->id,
                    'plot_planting_id' => $planting->id,
                    'viticulturist_id' => (int) $this->viticulturist_id,
                    'campaign_id'      => $campaign->id,
                    'activity_type'    => 'harvest',
                    'activity_date'    => $this->harvest_start_date,
                    'notes'            => $this->notes ?: null,
                ]);

                $weight    = (float) $this->total_weight;
                $pricePerKg = $this->price_per_kg ? (float) $this->price_per_kg : null;
                $totalValue = ($weight && $pricePerKg) ? round($weight * $pricePerKg, 3) : null;

                Harvest::create([
                    'activity_id'                => $activity->id,
                    'plot_planting_id'           => $planting->id,
                    'container_id'               => null,
                    'harvest_start_date'         => $this->harvest_start_date,
                    'total_weight'               => $weight,
                    'baume_degree'               => $this->baume_degree ?: null,
                    'brix_degree'                => $this->brix_degree ?: null,
                    'acidity_level'              => $this->acidity_level ?: null,
                    'ph_level'                   => $this->ph_level ?: null,
                    'health_status'              => $this->health_status ?: null,
                    'harvest_ticket_number'      => $this->harvest_ticket_number ?: null,
                    'sanitary_state_grapes'      => $this->sanitary_state_grapes ?: null,
                    'sanitary_state_agraces'     => $this->sanitary_state_agraces ?: null,
                    'sanitary_state_botrytis'    => $this->sanitary_state_botrytis ?: null,
                    'sanitary_state_oidium'      => $this->sanitary_state_oidium ?: null,
                    'sanitary_state_mildew'      => $this->sanitary_state_mildew ?: null,
                    'transport_document_number'  => $this->transport_document_number ?: null,
                    'destination_rega_code'      => $this->destination_rega_code ?: null,
                    'vehicle_plate'              => $this->vehicle_plate ?: null,
                    'destination_type'           => 'winery',
                    'price_per_kg'               => $pricePerKg,
                    'total_value'                => $totalValue,
                    'status'                     => 'active',
                    'notes'                      => $this->notes ?: null,
                ]);
            });

            $this->toastSuccess('Recepción de uva registrada correctamente.');
            redirect()->route('winery.grape-reception.index');
        } catch (\Exception $e) {
            \Log::error('Error al registrar recepción de uva', [
                'error'   => $e->getMessage(),
                'winery'  => Auth::id(),
                'planting'=> $this->plot_planting_id,
            ]);
            $this->toastError('Error al guardar la recepción. Inténtalo de nuevo.');
        }
    }

    public function render()
    {
        $wineryId = Auth::id();

        $linkedViticulturists = WineryViticulturist::where('winery_id', $wineryId)
            ->with('viticulturist:id,name')
            ->get()
            ->pluck('viticulturist')
            ->sortBy('name')
            ->values();

        return view('livewire.winery.harvest.reception.create', [
            'linkedViticulturists' => $linkedViticulturists,
        ])->layout('layouts.app');
    }
}
