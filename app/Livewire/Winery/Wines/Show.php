<?php

namespace App\Livewire\Winery\Wines;

use App\Livewire\Concerns\WithOwnershipRules;
use App\Livewire\Concerns\WithToastNotifications;
use App\Livewire\Winery\Wines\Traits\HasWineDiagram;
use App\Livewire\Winery\Wines\Traits\WithFermentationControlForm;
use App\Livewire\Winery\Wines\Traits\WithWineAdditiveForm;
use App\Livewire\Winery\Wines\Traits\WithWineAnalysisForm;
use App\Livewire\Winery\Wines\Traits\WithWineComposition;
use App\Livewire\Winery\Wines\Traits\WithWineLossForm;
use App\Livewire\Winery\Wines\Traits\WithWineTransferForm;
use App\Models\Container;
use App\Models\Harvest;
use App\Models\Oenologist;
use App\Models\UnitOfMeasurement;
use App\Models\Wine;
use App\Models\WinerySupply;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use HasWineDiagram,
        WithFermentationControlForm,
        WithOwnershipRules,
        WithToastNotifications,
        WithWineAdditiveForm,
        WithWineAnalysisForm,
        WithWineComposition,
        WithWineLossForm,
        WithWineTransferForm;

    public Wine $wine;

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

        $additives = $this->wine->additives()
            ->with(['supply', 'processDetail', 'oenologist', 'unitOfMeasurement:id,abbreviation'])
            ->get();

        $supplies = WinerySupply::where('user_id', Auth::id())->active()->orderBy('name')->get();
        $oenologists = Oenologist::where('user_id', Auth::id())->active()->orderBy('name')->get();
        $processes = $this->wine->processDetails()->orderBy('start_date')->get(['id', 'process_type', 'start_date']);

        $composition = $this->wine->wineHarvests()
            ->with([
                'harvest.plotPlanting.grapeVariety',
                'harvest.plotPlanting.plot',
            ])
            ->get();

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
}
