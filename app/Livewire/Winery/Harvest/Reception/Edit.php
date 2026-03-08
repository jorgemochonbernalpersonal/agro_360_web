<?php

namespace App\Livewire\Winery\Harvest\Reception;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\PlotPlanting;
use App\Models\WineryYieldForecast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Harvest $harvest;

    // Context (read-only)
    public string $viticulturistName = '';
    public string $plotName          = '';
    public string $plantingLabel     = '';
    public int    $vintageYear       = 0;

    // Reception data
    public string $harvest_start_date    = '';
    public string $harvest_time          = '';
    public string $harvest_ticket_number = '';
    public string $total_weight          = '';
    public string $price_per_kg          = '';

    // Quality
    public string $baume_degree      = '';
    public string $brix_degree       = '';
    public string $acidity_level     = '';
    public string $ph_level          = '';
    public string $potential_alcohol = '';

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

    // Destino bodega
    public string $container_id = '';

    // Descarte
    public bool   $disqualified        = false;
    public string $disqualified_reason = '';

    public string $notes = '';

    // Computed / dynamic
    public ?PlotPlanting $selectedPlanting       = null;
    public float         $totalHarvestedInCampaign = 0;
    public ?array        $harvestLimitInfo        = null;

    public function mount(Harvest $harvest): void
    {
        $wineryId = Auth::id();

        // Guard: solo recepciones que pertenecen a esta bodega
        abort_unless($harvest->winery_id === $wineryId, 403);

        $this->harvest = $harvest->load([
            'plotPlanting.grapeVariety',
            'plotPlanting.plot',
            'batch.viticulturist',
            'container',
        ]);

        $planting = $harvest->plotPlanting;

        // Context labels (ahora via batch, no via activity)
        $this->viticulturistName = $harvest->batch?->viticulturist?->name ?? '—';
        $this->plotName          = $planting?->plot?->name ?? '—';
        $this->plantingLabel     = ($planting?->grapeVariety?->name ?? $planting?->name ?? 'Sin variedad')
            . ($planting?->area_planted ? ' — ' . number_format($planting->area_planted, 2) . ' ha' : '');
        $this->vintageYear       = $harvest->vintage ?? $harvest->batch?->vintage_year ?? now()->year;

        // Pre-fill fields
        $this->harvest_start_date    = $harvest->harvest_start_date?->format('Y-m-d') ?? '';
        $this->harvest_time          = $harvest->harvest_time ?? '';
        $this->harvest_ticket_number = $harvest->harvest_ticket_number ?? '';
        $this->total_weight          = (string) ($harvest->total_weight ?? '');
        $this->price_per_kg          = (string) ($harvest->price_per_kg ?? '');
        $this->baume_degree          = (string) ($harvest->baume_degree ?? '');
        $this->brix_degree           = (string) ($harvest->brix_degree ?? '');
        $this->acidity_level         = (string) ($harvest->acidity_level ?? '');
        $this->ph_level              = (string) ($harvest->ph_level ?? '');
        $this->potential_alcohol     = (string) ($harvest->potential_alcohol ?? '');
        $this->health_status         = $harvest->health_status ?? '';
        $this->sanitary_state_grapes   = (string) ($harvest->sanitary_state_grapes ?? '');
        $this->sanitary_state_agraces  = (string) ($harvest->sanitary_state_agraces ?? '');
        $this->sanitary_state_botrytis = (string) ($harvest->sanitary_state_botrytis ?? '');
        $this->sanitary_state_oidium   = (string) ($harvest->sanitary_state_oidium ?? '');
        $this->sanitary_state_mildew   = (string) ($harvest->sanitary_state_mildew ?? '');
        $this->transport_document_number = $harvest->transport_document_number ?? '';
        $this->destination_rega_code     = $harvest->destination_rega_code ?? '';
        $this->vehicle_plate             = $harvest->vehicle_plate ?? '';
        $this->container_id              = (string) ($harvest->container_id ?? '');
        $this->disqualified              = (bool) $harvest->disqualified;
        $this->disqualified_reason       = $harvest->disqualified_reason ?? '';
        $this->notes                     = $harvest->notes ?? '';

        // Panel de límites: contar solo recepciones de ESTA bodega (excluir la propia para no doble-contar)
        $this->selectedPlanting = $planting;
        if ($planting) {
            $this->totalHarvestedInCampaign = max(
                0,
                $planting->getTotalWineryReceptionsForVintage($this->vintageYear, $wineryId) - (float) $this->total_weight
            );
            $this->updateLimitInfo();
        }
    }

    public function updatedTotalWeight(): void
    {
        $this->updateLimitInfo();
    }

    protected function updateLimitInfo(): void
    {
        if (!$this->selectedPlanting) {
            $this->harvestLimitInfo = null;
            return;
        }

        $effectiveLimit = $this->selectedPlanting->effectiveHarvestLimitKg($this->vintageYear);

        if ($effectiveLimit === null) {
            $this->harvestLimitInfo = null;
            return;
        }

        $harvested = $this->totalHarvestedInCampaign;
        $adding    = (float) ($this->total_weight ?: 0);
        $newTotal  = $harvested + $adding;
        $rawLimit  = (float) $this->selectedPlanting->harvest_limit_kg;

        $forecast = WineryYieldForecast::where('winery_id', Auth::id())
            ->where('plot_planting_id', $this->selectedPlanting->id)
            ->where('vintage_year', $this->vintageYear)
            ->where('status', 'confirmed')
            ->first();

        $forecastKg = $forecast ? (float) $forecast->estimated_kg : null;
        $opLimit    = $forecastKg !== null ? min($forecastKg, $effectiveLimit) : $effectiveLimit;

        $this->harvestLimitInfo = [
            'limit'        => $opLimit,
            'pac_limit'    => $effectiveLimit,
            'raw_limit'    => $rawLimit,
            'age_factor'   => $rawLimit > 0 ? round($effectiveLimit / $rawLimit * 100) : 100,
            'forecast_kg'  => $forecastKg,
            'has_forecast' => $forecastKg !== null,
            'harvested'    => $harvested,
            'adding'       => $adding,
            'new_total'    => $newTotal,
            'remaining'    => max(0, $opLimit - $newTotal),
            'percentage'   => $opLimit > 0 ? round(($newTotal / $opLimit) * 100, 1) : 0,
            'exceeds'      => $newTotal > $opLimit,
            'exceeds_pac'  => $newTotal > $effectiveLimit,
        ];
    }

    protected function rules(): array
    {
        return [
            'harvest_start_date'         => ['required', 'date'],
            'harvest_time'               => ['nullable', 'date_format:H:i'],
            'harvest_ticket_number'      => ['nullable', 'string', 'max:50'],
            'total_weight'               => ['required', 'numeric', 'min:0.01'],
            'price_per_kg'               => ['nullable', 'numeric', 'min:0'],
            'baume_degree'               => ['nullable', 'numeric', 'min:0', 'max:20'],
            'brix_degree'                => ['nullable', 'numeric', 'min:0', 'max:40'],
            'acidity_level'              => ['nullable', 'numeric', 'min:0', 'max:20'],
            'ph_level'                   => ['nullable', 'numeric', 'min:0', 'max:14'],
            'potential_alcohol'          => ['nullable', 'numeric', 'min:0', 'max:25'],
            'health_status'              => ['nullable', 'in:sano,daño_leve,daño_moderado,daño_grave'],
            'sanitary_state_grapes'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_agraces'     => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_botrytis'    => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_oidium'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sanitary_state_mildew'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'transport_document_number'  => ['nullable', 'string', 'max:50'],
            'destination_rega_code'      => ['nullable', 'string', 'max:20'],
            'vehicle_plate'              => ['nullable', 'string', 'max:20'],
            'container_id'               => ['required', 'exists:containers,id'],
            'disqualified'               => ['boolean'],
            'disqualified_reason'        => ['nullable', 'string', 'max:500'],
            'notes'                      => ['nullable', 'string'],
        ];
    }

    protected function messages(): array
    {
        return [
            'harvest_start_date.required' => 'La fecha de recepción es obligatoria.',
            'total_weight.required'       => 'El peso recibido es obligatorio.',
            'total_weight.min'            => 'El peso debe ser mayor que 0.',
            'container_id.required'       => 'Selecciona un depósito de destino.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $wineryId    = Auth::id();
        $weight      = (float) $this->total_weight;
        $pricePerKg  = $this->price_per_kg ? (float) $this->price_per_kg : null;
        $totalValue  = ($weight && $pricePerKg) ? round($weight * $pricePerKg, 3) : null;

        // Guard: container must belong to this winery
        $containerId = Container::where('user_id', $wineryId)
            ->findOrFail((int) $this->container_id)
            ->id;

        $oldWeight = (float) $this->harvest->total_weight;

        try {
            DB::transaction(function () use ($wineryId, $weight, $pricePerKg, $totalValue, $containerId, $oldWeight) {
                $this->harvest->update([
                    'harvest_start_date'         => $this->harvest_start_date,
                    'harvest_time'               => $this->harvest_time ?: null,
                    'harvest_ticket_number'      => $this->harvest_ticket_number ?: null,
                    'total_weight'               => $weight,
                    'price_per_kg'               => $pricePerKg,
                    'total_value'                => $totalValue,
                    'baume_degree'               => $this->baume_degree ?: null,
                    'brix_degree'                => $this->brix_degree ?: null,
                    'acidity_level'              => $this->acidity_level ?: null,
                    'ph_level'                   => $this->ph_level ?: null,
                    'potential_alcohol'          => $this->potential_alcohol ?: null,
                    'health_status'              => $this->health_status ?: null,
                    'sanitary_state_grapes'      => $this->sanitary_state_grapes ?: null,
                    'sanitary_state_agraces'     => $this->sanitary_state_agraces ?: null,
                    'sanitary_state_botrytis'    => $this->sanitary_state_botrytis ?: null,
                    'sanitary_state_oidium'      => $this->sanitary_state_oidium ?: null,
                    'sanitary_state_mildew'      => $this->sanitary_state_mildew ?: null,
                    'transport_document_number'  => $this->transport_document_number ?: null,
                    'destination_rega_code'      => $this->destination_rega_code ?: null,
                    'vehicle_plate'              => $this->vehicle_plate ?: null,
                    'container_id'               => $containerId,
                    'disqualified'               => $this->disqualified,
                    'disqualified_reason'        => ($this->disqualified && $this->disqualified_reason) ? $this->disqualified_reason : null,
                    'notes'                      => $this->notes ?: null,
                    'edited_at'                  => now(),
                    'edited_by'                  => $wineryId,
                ]);

                // Actualizar acumulado del batch si cambió el peso (dentro de la misma transacción)
                if ($oldWeight !== $weight && $this->harvest->batch_id) {
                    $this->harvest->batch?->recalculateTotal();
                }
            });

            $this->toastSuccess('Recepción actualizada correctamente.');
            redirect()->route('winery.grape-reception.index');

        } catch (\Exception $e) {
            \Log::error('Error al actualizar recepción de uva', [
                'harvest_id' => $this->harvest->id,
                'error'      => $e->getMessage(),
                'winery'     => Auth::id(),
            ]);
            $this->toastError('Error al guardar la recepción: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $availableContainers = Container::where('user_id', Auth::id())
            ->where('archived', false)
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'used_capacity']);

        return view('livewire.winery.harvest.reception.edit', [
            'availableContainers' => $availableContainers,
        ])->layout('layouts.app');
    }
}
