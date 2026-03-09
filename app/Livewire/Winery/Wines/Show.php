<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Container;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WineAnalysis;
use App\Models\WineFermentationControl;
use App\Models\WineLoss;
use App\Models\WineTransfer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public Wine $wine;

    // ── Formulario: control de fermentación ──────────────────────────────────
    public string $fc_container_id  = '';
    public string $fc_control_date  = '';
    public string $fc_temperature   = '';
    public string $fc_brix          = '';
    public string $fc_density       = '';
    public string $fc_ph            = '';
    public string $fc_va            = '';   // volatile acidity
    public string $fc_notes         = '';

    // ── Formulario: trasvase ──────────────────────────────────────────────────
    public string $tr_from_container_id = '';
    public string $tr_to_container_id   = '';
    public string $tr_quantity          = '';
    public string $tr_unit_id           = '';
    public string $tr_type              = 'racking';
    public string $tr_date              = '';
    public string $tr_notes             = '';

    // ── Formulario: merma ─────────────────────────────────────────────────────
    public string $lo_container_id = '';
    public string $lo_type         = 'evaporation';
    public string $lo_quantity     = '';
    public string $lo_unit_id      = '';
    public string $lo_date         = '';
    public string $lo_notes        = '';

    // ── Formulario: análisis ──────────────────────────────────────────────────
    public string $an_container_id      = '';
    public string $an_date              = '';
    public string $an_type              = 'own';
    public string $an_laboratory        = '';
    public string $an_alcohol           = '';
    public string $an_residual_sugar    = '';
    public string $an_total_acidity     = '';
    public string $an_volatile_acidity  = '';
    public string $an_ph                = '';
    public string $an_so2_free          = '';
    public string $an_so2_total         = '';
    public string $an_density           = '';
    public string $an_turbidity         = '';
    public string $an_notes             = '';

    public function mount(Wine $wine): void
    {
        abort_if($wine->user_id !== Auth::id(), 403);
        $this->wine = $wine;

        $now = now();
        $this->fc_control_date = $now->format('Y-m-d\TH:i');
        $this->tr_date         = $now->format('Y-m-d');
        $this->lo_date         = $now->format('Y-m-d');
        $this->an_date         = $now->format('Y-m-d');
    }

    // ─── Guardar control de fermentación ─────────────────────────────────────

    public function saveFermentationControl(): void
    {
        $this->validate([
            'fc_container_id' => ['required', 'exists:containers,id'],
            'fc_control_date' => ['required', 'date'],
            'fc_temperature'  => ['nullable', 'numeric', 'min:-20', 'max:100'],
            'fc_brix'         => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fc_density'      => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'fc_ph'           => ['nullable', 'numeric', 'min:2', 'max:7'],
            'fc_va'           => ['nullable', 'numeric', 'min:0'],
            'fc_notes'        => ['nullable', 'string'],
        ]);

        WineFermentationControl::create([
            'wine_id'          => $this->wine->id,
            'container_id'     => $this->fc_container_id,
            'control_date'     => $this->fc_control_date,
            'temperature'      => $this->fc_temperature ?: null,
            'brix_degree'      => $this->fc_brix ?: null,
            'density'          => $this->fc_density ?: null,
            'ph'               => $this->fc_ph ?: null,
            'volatile_acidity' => $this->fc_va ?: null,
            'notes'            => $this->fc_notes ?: null,
            'created_by'       => Auth::id(),
        ]);

        $this->resetFcForm();
        $this->dispatch('close-modal', id: 'modal-fermentation');
        $this->toastSuccess('Control de fermentación registrado.');
    }

    public function deleteFermentationControl(int $id): void
    {
        WineFermentationControl::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess('Control eliminado.');
    }

    // ─── Guardar trasvase ─────────────────────────────────────────────────────

    public function saveTransfer(): void
    {
        $this->validate([
            'tr_from_container_id' => ['nullable', 'exists:containers,id'],
            'tr_to_container_id'   => ['required', 'exists:containers,id', 'different:tr_from_container_id'],
            'tr_quantity'          => ['required', 'numeric', 'min:0.001'],
            'tr_unit_id'           => ['required', 'exists:units_of_measurement,id'],
            'tr_type'              => ['required', 'in:' . implode(',', array_keys(WineTransfer::TRANSFER_TYPES))],
            'tr_date'              => ['required', 'date'],
            'tr_notes'             => ['nullable', 'string'],
        ]);

        WineTransfer::create([
            'wine_id'              => $this->wine->id,
            'from_container_id'    => $this->tr_from_container_id ?: null,
            'to_container_id'      => $this->tr_to_container_id,
            'quantity'             => $this->tr_quantity,
            'unit_of_measurement_id' => $this->tr_unit_id,
            'transfer_type'        => $this->tr_type,
            'transfer_date'        => $this->tr_date,
            'notes'                => $this->tr_notes ?: null,
            'created_by'           => Auth::id(),
        ]);

        $this->resetTrForm();
        $this->dispatch('close-modal', id: 'modal-transfer');
        $this->toastSuccess('Trasvase registrado.');
    }

    public function deleteTransfer(int $id): void
    {
        WineTransfer::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess('Trasvase eliminado.');
    }

    // ─── Guardar merma ────────────────────────────────────────────────────────

    public function saveLoss(): void
    {
        $this->validate([
            'lo_container_id' => ['nullable', 'exists:containers,id'],
            'lo_type'         => ['required', 'in:' . implode(',', array_keys(WineLoss::LOSS_TYPES))],
            'lo_quantity'     => ['required', 'numeric', 'min:0.001'],
            'lo_unit_id'      => ['required', 'exists:units_of_measurement,id'],
            'lo_date'         => ['required', 'date'],
            'lo_notes'        => ['nullable', 'string'],
        ]);

        WineLoss::create([
            'wine_id'                => $this->wine->id,
            'container_id'           => $this->lo_container_id ?: null,
            'loss_type'              => $this->lo_type,
            'quantity'               => $this->lo_quantity,
            'unit_of_measurement_id' => $this->lo_unit_id,
            'loss_date'              => $this->lo_date,
            'notes'                  => $this->lo_notes ?: null,
            'created_by'             => Auth::id(),
        ]);

        $this->resetLoForm();
        $this->dispatch('close-modal', id: 'modal-loss');
        $this->toastSuccess('Merma registrada.');
    }

    public function deleteLoss(int $id): void
    {
        WineLoss::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess('Merma eliminada.');
    }

    // ─── Guardar análisis ─────────────────────────────────────────────────────

    public function saveAnalysis(): void
    {
        $this->validate([
            'an_container_id'     => ['nullable', 'exists:containers,id'],
            'an_date'             => ['required', 'date'],
            'an_type'             => ['required', 'in:own,external'],
            'an_laboratory'       => ['nullable', 'string', 'max:200'],
            'an_alcohol'          => ['nullable', 'numeric', 'min:0', 'max:25'],
            'an_residual_sugar'   => ['nullable', 'numeric', 'min:0'],
            'an_total_acidity'    => ['nullable', 'numeric', 'min:0'],
            'an_volatile_acidity' => ['nullable', 'numeric', 'min:0'],
            'an_ph'               => ['nullable', 'numeric', 'min:2', 'max:7'],
            'an_so2_free'         => ['nullable', 'numeric', 'min:0'],
            'an_so2_total'        => ['nullable', 'numeric', 'min:0'],
            'an_density'          => ['nullable', 'numeric', 'min:0.900', 'max:1.200'],
            'an_turbidity'        => ['nullable', 'numeric', 'min:0'],
            'an_notes'            => ['nullable', 'string'],
        ]);

        WineAnalysis::create([
            'wine_id'              => $this->wine->id,
            'container_id'         => $this->an_container_id ?: null,
            'analysis_date'        => $this->an_date,
            'analysis_type'        => $this->an_type,
            'laboratory_name'      => $this->an_laboratory ?: null,
            'alcohol'              => $this->an_alcohol ?: null,
            'residual_sugar'       => $this->an_residual_sugar ?: null,
            'total_acidity'        => $this->an_total_acidity ?: null,
            'volatile_acidity'     => $this->an_volatile_acidity ?: null,
            'ph'                   => $this->an_ph ?: null,
            'so2_free'             => $this->an_so2_free ?: null,
            'so2_total'            => $this->an_so2_total ?: null,
            'density'              => $this->an_density ?: null,
            'turbidity'            => $this->an_turbidity ?: null,
            'notes'                => $this->an_notes ?: null,
            'created_by'           => Auth::id(),
        ]);

        $this->resetAnForm();
        $this->dispatch('close-modal', id: 'modal-analysis');
        $this->toastSuccess('Análisis registrado.');
    }

    public function deleteAnalysis(int $id): void
    {
        WineAnalysis::where('wine_id', $this->wine->id)->findOrFail($id)->delete();
        $this->toastSuccess('Análisis eliminado.');
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
            ->with(['fromContainer:id,name', 'toContainer:id,name', 'unitOfMeasurement:id,abbreviation'])
            ->get();

        $losses = $this->wine->losses()
            ->with(['container:id,name', 'unitOfMeasurement:id,abbreviation'])
            ->get();

        $analyses = $this->wine->analyses()
            ->with('container:id,name')
            ->get();

        $timeline = $this->buildTimeline($fermentationControls, $transfers, $losses, $analyses);

        return view('livewire.winery.wines.show', compact(
            'containers', 'units',
            'fermentationControls', 'transfers', 'losses', 'analyses',
            'timeline'
        ))->layout('layouts.app');
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

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
            ->groupBy(fn($item) => $item['date']->format('Y-m-d'))
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

    private function resetAnForm(): void
    {
        $this->an_container_id = $this->an_date = $this->an_laboratory = '';
        $this->an_type = 'own';
        $this->an_alcohol = $this->an_residual_sugar = $this->an_total_acidity = '';
        $this->an_volatile_acidity = $this->an_ph = $this->an_so2_free = '';
        $this->an_so2_total = $this->an_density = $this->an_turbidity = $this->an_notes = '';
        $this->an_date = now()->format('Y-m-d');
    }
}
