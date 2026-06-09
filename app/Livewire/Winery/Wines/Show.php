<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineAdditive;
use App\Models\WineAnalysis;
use App\Models\WineFermentationControl;
use App\Models\WineHarvest;
use App\Models\WineLoss;
use App\Models\WinerySupply;
use App\Models\WineTransfer;
use App\Services\WineContainerStockService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Show extends Component
{
    use WithOwnershipRules, WithToastNotifications;

    public Wine $wine;

    // ── Formulario: control de fermentación ──────────────────────────────────
    public string $fc_container_id = '';

    public string $fc_control_date = '';

    public string $fc_temperature = '';

    public string $fc_brix = '';

    public string $fc_density = '';

    public string $fc_ph = '';

    public string $fc_va = '';   // volatile acidity

    public string $fc_notes = '';

    // ── Formulario: trasvase ──────────────────────────────────────────────────
    public string $tr_from_container_id = '';

    public string $tr_to_container_id = '';

    public string $tr_quantity = '';

    public string $tr_unit_id = '';

    public string $tr_type = 'racking';

    public string $tr_date = '';

    public string $tr_oenologist_id = '';

    public string $tr_notes = '';

    // ── Formulario: merma ─────────────────────────────────────────────────────
    public string $lo_container_id = '';

    public string $lo_type = 'evaporation';

    public string $lo_quantity = '';

    public string $lo_unit_id = '';

    public string $lo_date = '';

    public string $lo_notes = '';

    // ── Formulario: composición (uva → vino) ─────────────────────────────────
    public string $co_harvest_id = '';

    public string $co_quantity_kg = '';

    // ── Formulario: aditivos ──────────────────────────────────────────────────
    public string $ad_supply_id = '';

    public string $ad_process_detail_id = '';

    public string $ad_oenologist_id = '';

    public string $ad_additive_name = '';

    public string $ad_quantity = '';

    public string $ad_unit_id = '';

    public string $ad_date = '';

    public string $ad_notes = '';

    // ── Formulario: análisis ──────────────────────────────────────────────────
    public string $an_container_id = '';

    public string $an_oenologist_id = '';

    public string $an_date = '';

    public string $an_type = 'own';

    public string $an_laboratory = '';

    public string $an_alcohol = '';

    public string $an_residual_sugar = '';

    public string $an_total_acidity = '';

    public string $an_volatile_acidity = '';

    public string $an_ph = '';

    public string $an_so2_free = '';

    public string $an_so2_total = '';

    public string $an_density = '';

    public string $an_turbidity = '';

    public string $an_color_intensity = '';

    public string $an_malic_acid = '';

    public string $an_notes = '';

    public function mount(Wine $wine): void
    {
        $this->authorize('view', $wine);
        $this->wine = $wine;

        $now = now();
        $this->fc_control_date = $now->format('Y-m-d\TH:i');
        $this->tr_date = $now->format('Y-m-d');
        $this->lo_date = $now->format('Y-m-d');
        $this->an_date = $now->format('Y-m-d');
        $this->ad_date = $now->format('Y-m-d');
    }

    // ─── Guardar control de fermentación ─────────────────────────────────────

    public function saveFermentationControl(): void
    {
        $this->validate([
            'fc_container_id' => $this->ownedContainerRule(),
            'fc_control_date' => ['required', 'date'],
            'fc_temperature' => ['nullable', 'numeric', 'min:-20', 'max:100'],
            'fc_brix' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fc_density' => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'fc_ph' => ['nullable', 'numeric', 'min:2', 'max:7'],
            'fc_va' => ['nullable', 'numeric', 'min:0'],
            'fc_notes' => ['nullable', 'string'],
        ]);

        WineFermentationControl::create([
            'wine_id' => $this->wine->id,
            'container_id' => $this->fc_container_id,
            'control_date' => $this->fc_control_date,
            'temperature' => $this->fc_temperature ?: null,
            'brix_degree' => $this->fc_brix ?: null,
            'density' => $this->fc_density ?: null,
            'ph' => $this->fc_ph ?: null,
            'volatile_acidity' => $this->fc_va ?: null,
            'notes' => $this->fc_notes ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->resetFcForm();
        $this->dispatch('close-modal', id: 'modal-fermentation');
        $this->toastSuccess(__('Control de fermentación registrado.'));
    }

    public function deleteFermentationControl(int $id): void
    {
        WineFermentationControl::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess(__('Control eliminado.'));
    }

    // ─── Guardar trasvase ─────────────────────────────────────────────────────

    public function saveTransfer(): void
    {
        $this->validate([
            'tr_from_container_id' => $this->ownedContainerRule(false),
            'tr_to_container_id' => [...$this->ownedContainerRule(), 'different:tr_from_container_id'],
            'tr_quantity' => ['required', 'numeric', 'min:0.001'],
            'tr_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'tr_type' => ['required', 'in:'.implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'tr_date' => ['required', 'date'],
            'tr_oenologist_id' => $this->ownedOenologistRule(),
            'tr_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () {
                $transfer = WineTransfer::create([
                    'wine_id' => $this->wine->id,
                    'from_container_id' => $this->tr_from_container_id ?: null,
                    'to_container_id' => $this->tr_to_container_id,
                    'quantity' => $this->tr_quantity,
                    'unit_of_measurement_id' => $this->tr_unit_id,
                    'transfer_type' => $this->tr_type,
                    'transfer_date' => $this->tr_date,
                    'oenologist_id' => $this->tr_oenologist_id ?: null,
                    'notes' => $this->tr_notes ?: null,
                    'created_by' => Auth::id(),
                ]);
                app(WineContainerStockService::class)->recordTransfer($transfer);
            });
        } catch (\RuntimeException $e) {
            $this->addError('tr_quantity', $e->getMessage());

            return;
        }

        $this->resetTrForm();
        $this->dispatch('close-modal', id: 'modal-transfer');
        $this->toastSuccess(__('Trasvase registrado.'));
    }

    public function deleteTransfer(int $id): void
    {
        $transfer = WineTransfer::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($transfer) {
            app(WineContainerStockService::class)->revertTransfer($transfer);
            $transfer->delete();
        });

        $this->toastSuccess(__('Trasvase eliminado.'));
    }

    // ─── Guardar merma ────────────────────────────────────────────────────────

    public function saveLoss(): void
    {
        $this->validate([
            'lo_container_id' => $this->ownedContainerRule(false),
            'lo_type' => ['required', 'in:'.implode(',', array_keys(WineLoss::LOSS_TYPES))],
            'lo_quantity' => ['required', 'numeric', 'min:0.001'],
            'lo_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'lo_date' => ['required', 'date'],
            'lo_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::transaction(function () {
                $loss = WineLoss::create([
                    'wine_id' => $this->wine->id,
                    'container_id' => $this->lo_container_id ?: null,
                    'loss_type' => $this->lo_type,
                    'quantity' => $this->lo_quantity,
                    'unit_of_measurement_id' => $this->lo_unit_id,
                    'loss_date' => $this->lo_date,
                    'notes' => $this->lo_notes ?: null,
                    'created_by' => Auth::id(),
                ]);
                app(WineContainerStockService::class)->recordLoss($loss);
            });
        } catch (\RuntimeException $e) {
            $this->addError('lo_quantity', $e->getMessage());

            return;
        }

        $this->resetLoForm();
        $this->dispatch('close-modal', id: 'modal-loss');
        $this->toastSuccess(__('Merma registrada.'));
    }

    public function deleteLoss(int $id): void
    {
        $loss = WineLoss::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($loss) {
            app(WineContainerStockService::class)->revertLoss($loss);
            $loss->delete();
        });

        $this->toastSuccess(__('Merma eliminada.'));
    }

    // ─── Guardar análisis ─────────────────────────────────────────────────────

    public function saveAnalysis(): void
    {
        $this->validate([
            'an_container_id' => $this->ownedContainerRule(false),
            'an_oenologist_id' => $this->ownedOenologistRule(),
            'an_date' => ['required', 'date'],
            'an_type' => ['required', 'in:own,external'],
            'an_laboratory' => ['nullable', 'string', 'max:200'],
            'an_alcohol' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'an_residual_sugar' => ['nullable', 'numeric', 'min:0'],
            'an_total_acidity' => ['nullable', 'numeric', 'min:0'],
            'an_volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'an_ph' => ['nullable', 'numeric', 'min:2', 'max:7'],
            'an_so2_free' => ['nullable', 'numeric', 'min:0'],
            'an_so2_total' => ['nullable', 'numeric', 'min:0'],
            'an_density' => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'an_turbidity' => ['nullable', 'numeric', 'min:0'],
            'an_color_intensity' => ['nullable', 'numeric', 'min:0'],
            'an_malic_acid' => ['nullable', 'numeric', 'min:0'],
            'an_notes' => ['nullable', 'string'],
        ]);

        WineAnalysis::create([
            'wine_id' => $this->wine->id,
            'container_id' => $this->an_container_id ?: null,
            'oenologist_id' => $this->an_oenologist_id ?: null,
            'analysis_date' => $this->an_date,
            'analysis_type' => $this->an_type,
            'laboratory_name' => $this->an_laboratory ?: null,
            'alcohol' => $this->an_alcohol ?: null,
            'residual_sugar' => $this->an_residual_sugar ?: null,
            'total_acidity' => $this->an_total_acidity ?: null,
            'volatile_acidity' => $this->an_volatile_acidity ?: null,
            'ph' => $this->an_ph ?: null,
            'so2_free' => $this->an_so2_free ?: null,
            'so2_total' => $this->an_so2_total ?: null,
            'density' => $this->an_density ?: null,
            'turbidity' => $this->an_turbidity ?: null,
            'color_intensity' => $this->an_color_intensity ?: null,
            'malic_acid' => $this->an_malic_acid ?: null,
            'notes' => $this->an_notes ?: null,
            'created_by' => Auth::id(),
        ]);

        $this->resetAnForm();
        $this->dispatch('close-modal', id: 'modal-analysis');
        $this->toastSuccess(__('Análisis registrado.'));
    }

    public function deleteAnalysis(int $id): void
    {
        WineAnalysis::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess(__('Análisis eliminado.'));
    }

    // ─── Aditivos ─────────────────────────────────────────────────────────────

    public function updatedAdSupplyId(): void
    {
        if ($this->ad_supply_id) {
            $supply = WinerySupply::find($this->ad_supply_id);
            if ($supply) {
                $this->ad_additive_name = $supply->name;
                $this->ad_unit_id = (string) ($supply->unit_of_measurement_id ?? '');
            }
        }
    }

    public function saveAdditive(): void
    {
        $this->validate([
            'ad_additive_name' => ['required', 'string', 'max:200'],
            'ad_quantity' => ['required', 'numeric', 'min:0.001'],
            'ad_unit_id' => ['required', 'exists:units_of_measurement,id'],
            'ad_date' => ['required', 'date'],
            'ad_supply_id' => $this->ownedWinerySupplyRule(false),
            'ad_process_detail_id' => $this->ownedWineProcessDetailRule(false),
            'ad_oenologist_id' => $this->ownedOenologistRule(),
            'ad_notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () {
            WineAdditive::create([
                'wine_id' => $this->wine->id,
                'wine_process_detail_id' => $this->ad_process_detail_id ?: null,
                'winery_supply_id' => $this->ad_supply_id ?: null,
                'oenologist_id' => $this->ad_oenologist_id ?: null,
                'unit_of_measurement_id' => $this->ad_unit_id,
                'additive_name' => $this->ad_additive_name,
                'quantity' => $this->ad_quantity,
                'application_date' => $this->ad_date,
                'notes' => $this->ad_notes ?: null,
                'created_by' => Auth::id(),
            ]);

            // Descuenta stock del insumo si está vinculado
            if ($this->ad_supply_id) {
                $supply = WinerySupply::where('user_id', Auth::id())->lockForUpdate()->find($this->ad_supply_id);
                if ($supply) {
                    $supply->decrement('current_stock', (float) $this->ad_quantity);
                }
            }
        });

        $this->resetAdForm();
        $this->dispatch('close-modal', id: 'modal-additive');
        $this->toastSuccess(__('Aditivo registrado correctamente.'));
    }

    public function deleteAdditive(int $id): void
    {
        $additive = WineAdditive::where('wine_id', $this->wine->id)->findOrFail($id);

        DB::transaction(function () use ($additive) {
            // Restaura stock si tenía supply vinculado
            if ($additive->winery_supply_id) {
                $supply = WinerySupply::where('user_id', Auth::id())->lockForUpdate()->find($additive->winery_supply_id);
                if ($supply) {
                    $supply->increment('current_stock', (float) $additive->quantity);
                }
            }
            $additive->delete();
        });

        $this->toastSuccess(__('Aditivo eliminado.'));
    }

    // ─── Composición ─────────────────────────────────────────────────────────

    public function linkHarvest(): void
    {
        $this->validate([
            'co_harvest_id' => $this->ownedHarvestRule(),
            'co_quantity_kg' => ['required', 'numeric', 'min:0.001'],
        ]);

        // Verify the harvest belongs to this winery
        $harvest = Harvest::where('winery_id', Auth::id())->findOrFail($this->co_harvest_id);

        WineHarvest::updateOrCreate(
            ['wine_id' => $this->wine->id, 'harvest_id' => $harvest->id],
            ['quantity_kg' => $this->co_quantity_kg]
        );

        $this->recalculateCompositionPercentages();
        $this->co_harvest_id = '';
        $this->co_quantity_kg = '';
        $this->dispatch('close-modal', id: 'modal-composition');
        $this->toastSuccess(__('Recepción vinculada al lote.'));
    }

    public function unlinkHarvest(int $wineHarvestId): void
    {
        WineHarvest::where('wine_id', $this->wine->id)->findOrFail($wineHarvestId)->delete();
        $this->recalculateCompositionPercentages();
        $this->toastSuccess(__('Recepción desvinculada.'));
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $this->wine->refresh();

        $containers = Container::where('user_id', Auth::id())
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'capacity', 'used_capacity']);

        $units = UnitOfMeasurement::whereIn('name', ['Litros', 'Kilogramos', 'Hectolitros'])
            ->orWhereIn('abbreviation', ['L', 'kg', 'hl'])
            ->get();

        $fermentationControls = $this->wine->fermentationControls()
            ->with('container:id,name')
            ->get();

        $transfers = $this->wine->transfers()
            ->with(['fromContainer:id,name', 'toContainer:id,name', 'unitOfMeasurement:id,abbreviation', 'oenologist:id,name,surname'])
            ->get();

        $losses = $this->wine->losses()
            ->with(['container:id,name', 'unitOfMeasurement:id,abbreviation'])
            ->get();

        $analyses = $this->wine->analyses()
            ->with(['container:id,name', 'oenologist:id,name,surname'])
            ->get();

        $timeline = $this->buildTimeline($fermentationControls, $transfers, $losses, $analyses);

        // Aditivos
        $additives = $this->wine->additives()
            ->with(['supply', 'processDetail', 'oenologist', 'unitOfMeasurement:id,abbreviation'])
            ->get();

        $supplies = WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get();
        $oenologists = Oenologist::where('user_id', Auth::id())->active()->orderBy('name')->get();
        $processes = $this->wine->processDetails()->orderBy('start_date')->get(['id', 'process_type', 'start_date']);

        // Composición: recepciones vinculadas
        $composition = $this->wine->wineHarvests()
            ->with([
                'harvest.plotPlanting.plotVariety.grapeVariety',
                'harvest.plotPlanting.plot',
            ])
            ->get();

        // Recepciones disponibles de esta bodega (excluye las ya vinculadas)
        $linkedHarvestIds = $composition->pluck('harvest_id')->all();
        $availableHarvests = Harvest::where('winery_id', Auth::id())
            ->whereNotIn('id', $linkedHarvestIds)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('harvest_start_date')
            ->get(['id', 'harvest_start_date', 'total_weight', 'plot_planting_id', 'vintage']);

        $costs = $this->wine->costs()->orderByDesc('cost_date')->get();
        $diagram = $this->buildDiagram($composition, $processes, $transfers, $losses, $analyses);
        $qrSvg = $this->generateQrSvg();
        $traceUrl = route('wine.trace', $this->wine->trace_token);

        return view('livewire.winery.wines.show', compact(
            'containers', 'units',
            'fermentationControls', 'transfers', 'losses', 'analyses',
            'timeline', 'composition', 'availableHarvests',
            'additives', 'supplies', 'oenologists', 'processes',
            'costs', 'diagram', 'qrSvg', 'traceUrl'
        ))->layout('layouts.app');
    }

    private function recalculateCompositionPercentages(): void
    {
        $entries = WineHarvest::where('wine_id', $this->wine->id)->get();
        $total = $entries->sum('quantity_kg');

        if ($total <= 0) {
            return;
        }

        foreach ($entries as $entry) {
            $entry->update(['percentage' => round(($entry->quantity_kg / $total) * 100, 2)]);
        }
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function generateQrSvg(): string
    {
        if (! $this->wine->trace_token) {
            $this->wine->trace_token = (string) Str::uuid();
            $this->wine->saveQuietly();
        }

        $url = route('wine.trace', $this->wine->trace_token);

        $renderer = new ImageRenderer(
            new RendererStyle(280),
            new SvgImageBackEnd
        );

        return (new Writer($renderer))->writeString($url);
    }

    private function buildDiagram($composition, $processes, $transfers, $losses, $analyses): string
    {
        $lines = ['graph LR'];

        // ── Inputs: recepciones de uva ────────────────────────────────────────
        foreach ($composition as $i => $entry) {
            $variety = $entry->harvest->plotPlanting?->plotVariety?->grapeVariety?->name ?? 'Uva';
            $kg = number_format($entry->quantity_kg ?? 0, 0);
            $nodeId = 'grape_'.$i;
            $label = addslashes("{$variety}\n{$kg} kg");
            $lines[] = "    {$nodeId}([\"🍇 {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#d1fae5,stroke:#059669,color:#065f46";
            $lines[] = "    {$nodeId} --> wine";
        }

        // ── Nodo central: el vino ─────────────────────────────────────────────
        $wineName = addslashes($this->wine->name);
        $lines[] = "    wine[\"🍷 {$wineName}\"]";
        $lines[] = '    style wine fill:#ede9fe,stroke:#7c3aed,color:#4c1d95,font-weight:bold';

        // ── Etapas de proceso ─────────────────────────────────────────────────
        $processLabels = [
            'destemming_crushing' => __('Despalillado/Estrujado'),
            'pressing' => __('Prensado'),
            'settling' => __('Desfangado'),
            'fermentation' => __('Fermentación'),
            'maceration' => __('Maceración'),
            'malolactic' => __('Maloláctica'),
            'aging' => __('Crianza'),
            'racking' => __('Trasiego'),
            'blending' => __('Coupage'),
            'fining' => __('Clarificación'),
            'filtration' => __('Filtración'),
            'cold_stabilization' => __('Est. en frío'),
            'bottling' => __('Embotellado'),
            'other' => __('Otro'),
        ];

        $prevNode = 'wine';
        foreach ($processes as $i => $proc) {
            $nodeId = 'proc_'.$i;
            $label = $processLabels[$proc->process_type] ?? $proc->process_type;
            $date = $proc->start_date ? \Carbon\Carbon::parse($proc->start_date)->format('d/m') : '';
            $lines[] = "    {$nodeId}[\"⚙️ {$label}".($date ? "\n{$date}" : '').'"]';
            $lines[] = "    style {$nodeId} fill:#dbeafe,stroke:#2563eb,color:#1e3a8a";
            $lines[] = "    {$prevNode} --> {$nodeId}";
            $prevNode = $nodeId;
        }

        // ── Trasvases ─────────────────────────────────────────────────────────
        foreach ($transfers as $i => $tr) {
            $from = $tr->fromContainer?->name ?? '?';
            $to = $tr->toContainer?->name ?? '?';
            $nodeId = 'tr_'.$i;
            $label = addslashes("Trasvase\n{$from} → {$to}");
            $lines[] = "    {$nodeId}[\"↔️ {$label}\"]";
            $lines[] = "    style {$nodeId} fill:#fef9c3,stroke:#ca8a04,color:#78350f";
            $lines[] = "    wine --> {$nodeId}";
        }

        // ── Mermas ────────────────────────────────────────────────────────────
        foreach ($losses as $i => $lo) {
            $nodeId = 'loss_'.$i;
            $qty = number_format($lo->quantity ?? 0, 1);
            $unit = $lo->unitOfMeasurement?->abbreviation ?? '';
            $label = addslashes("Merma\n{$qty} {$unit}");
            $lines[] = "    {$nodeId}([\"⚠️ {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#fee2e2,stroke:#dc2626,color:#7f1d1d";
            $lines[] = "    wine -.-> {$nodeId}";
        }

        // ── Análisis ──────────────────────────────────────────────────────────
        foreach ($analyses as $i => $an) {
            $nodeId = 'an_'.$i;
            $date = $an->analysis_date ? $an->analysis_date->format('d/m/y') : '';
            $label = addslashes("Análisis\n{$date}");
            $lines[] = "    {$nodeId}([\"🔬 {$label}\"])";
            $lines[] = "    style {$nodeId} fill:#fef3c7,stroke:#d97706,color:#78350f";
            $lines[] = "    wine -.-> {$nodeId}";
        }

        return implode("\n", $lines);
    }

    private function buildTimeline($fermentationControls, $transfers, $losses, $analyses): array
    {
        $items = collect();

        foreach ($fermentationControls as $fc) {
            $items->push(['type' => 'fermentation', 'date' => $fc->control_date, 'model' => $fc]);
        }
        foreach ($transfers as $tr) {
            $items->push(['type' => 'transfer', 'date' => $tr->transfer_date->startOfDay(), 'model' => $tr]);
        }
        foreach ($losses as $lo) {
            $items->push(['type' => 'loss', 'date' => $lo->loss_date->startOfDay(), 'model' => $lo]);
        }
        foreach ($analyses as $an) {
            $items->push(['type' => 'analysis', 'date' => $an->analysis_date->startOfDay(), 'model' => $an]);
        }

        return $items
            ->sortByDesc('date')
            ->groupBy(fn ($item) => $item['date']->format('Y-m-d'))
            ->all();
    }

    private function resetFcForm(): void
    {
        $this->fc_container_id = '';
        $this->fc_control_date = now()->format('Y-m-d\TH:i');
        $this->fc_temperature = $this->fc_brix = $this->fc_density = '';
        $this->fc_ph = $this->fc_va = $this->fc_notes = '';
    }

    private function resetTrForm(): void
    {
        $this->tr_from_container_id = $this->tr_to_container_id = '';
        $this->tr_quantity = $this->tr_unit_id = $this->tr_notes = '';
        $this->tr_oenologist_id = '';
        $this->tr_type = 'racking';
        $this->tr_date = now()->format('Y-m-d');
    }

    private function resetLoForm(): void
    {
        $this->lo_container_id = '';
        $this->lo_type = 'evaporation';
        $this->lo_quantity = $this->lo_unit_id = $this->lo_notes = '';
        $this->lo_date = now()->format('Y-m-d');
    }

    private function resetAdForm(): void
    {
        $this->ad_supply_id = $this->ad_process_detail_id = $this->ad_oenologist_id = '';
        $this->ad_additive_name = $this->ad_quantity = $this->ad_unit_id = $this->ad_notes = '';
        $this->ad_date = now()->format('Y-m-d');
    }

    private function resetCoForm(): void
    {
        $this->co_harvest_id = '';
        $this->co_quantity_kg = '';
    }

    private function resetAnForm(): void
    {
        $this->an_container_id = $this->an_oenologist_id = $this->an_laboratory = '';
        $this->an_type = 'own';
        $this->an_alcohol = $this->an_residual_sugar = $this->an_total_acidity = '';
        $this->an_volatile_acidity = $this->an_ph = $this->an_so2_free = '';
        $this->an_so2_total = $this->an_density = $this->an_turbidity = '';
        $this->an_color_intensity = $this->an_malic_acid = $this->an_notes = '';
        $this->an_date = now()->format('Y-m-d');
    }
}
