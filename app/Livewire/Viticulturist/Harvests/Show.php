<?php

namespace App\Livewire\Viticulturist\Harvests;

use App\Models\Harvest;
use App\Models\HarvestDelivery;
use App\Models\PlotPlanting;
use App\Notifications\HarvestDeliveryDisputedNotification;
use App\Livewire\Concerns\WithToastNotifications;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    use WithToastNotifications;

    public PlotPlanting $planting;
    public int $vintageYear;

    // Dispute form
    public string $disputeNote     = '';
    public ?int   $disputingId     = null; // ID de la HarvestDelivery que se está reclamando

    public function mount(PlotPlanting $planting): void
    {
        abort_unless(
            $planting->plot->viticulturist_id === Auth::id(),
            403
        );

        $this->planting    = $planting;
        $this->vintageYear = (int) request('vintage', now()->year);
    }

    public function openDispute(int $deliveryId): void
    {
        $this->disputingId = $deliveryId;
        $this->disputeNote = '';
    }

    public function cancelDispute(): void
    {
        $this->disputingId = null;
        $this->disputeNote = '';
    }

    public function submitDispute(): void
    {
        $this->validate([
            'disputeNote' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'disputeNote.required' => 'Explica el motivo de la reclamación.',
            'disputeNote.min'      => 'La nota debe tener al menos 10 caracteres.',
            'disputeNote.max'      => 'La nota no puede superar los 1000 caracteres.',
        ]);

        $delivery = HarvestDelivery::where('viticulturist_id', Auth::id())
            ->where('status', 'disputed')
            ->whereNull('dispute_submitted_at')
            ->findOrFail($this->disputingId);

        $delivery->update([
            'dispute_note'         => $this->disputeNote,
            'dispute_submitted_at' => now(),
        ]);

        // Notificar a la bodega si la recepción está enlazada
        $notified = false;
        if ($delivery->harvest_id) {
            $winery = $delivery->harvest?->winery;
            if ($winery?->email) {
                $winery->notify(new HarvestDeliveryDisputedNotification($delivery));
                $notified = true;
            }
        }

        $this->disputingId = null;
        $this->disputeNote = '';

        $this->toastSuccess($notified
            ? 'Reclamación enviada. La bodega recibirá una notificación.'
            : 'Reclamación registrada. No se pudo notificar a la bodega porque la entrega no está vinculada a una recepción.'
        );
    }

    public function render()
    {
        $viticulturistId = Auth::id();

        $notebookHarvests = Harvest::with(['activity'])
            ->whereHas('activity', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where('plot_planting_id', $this->planting->id)
            ->where('vintage', $this->vintageYear)
            ->where('status', 'active')
            ->orderBy('harvest_start_date')
            ->get();

        $wineryReceptions = Harvest::with(['winery:id,name', 'delivery'])
            ->whereNotNull('winery_id')
            ->where('plot_planting_id', $this->planting->id)
            ->where('vintage', $this->vintageYear)
            ->where('status', 'active')
            ->orderBy('harvest_start_date')
            ->get();

        $declaredDeliveries = HarvestDelivery::with(['harvest'])
            ->forViticulturist($viticulturistId)
            ->where('plot_planting_id', $this->planting->id)
            ->where('vintage_year', $this->vintageYear)
            ->orderBy('delivery_date')
            ->get();

        $totalNotebook    = (float) $notebookHarvests->sum('total_weight');
        $totalWinery      = (float) $wineryReceptions->sum('total_weight');
        $totalDeclared    = (float) $declaredDeliveries->where('disqualified', false)->sum('delivered_kg');
        $globalDiscrepancy = $totalNotebook > 0 && $totalWinery > 0
            ? round(abs($totalNotebook - $totalWinery), 1)
            : null;

        // Cupo PAC
        $cupoKg       = $this->planting->effectiveHarvestLimitKg($this->vintageYear);
        $totalRef     = $totalWinery > 0 ? $totalWinery : $totalDeclared; // base real para % uso
        $cupoPct      = ($cupoKg && $cupoKg > 0 && $totalRef > 0)
            ? round($totalRef / $cupoKg * 100, 1)
            : null;
        $cupoExceeded = $cupoPct !== null && $cupoPct > 100;

        return view('livewire.viticulturist.harvests.show', [
            'notebookHarvests'   => $notebookHarvests,
            'wineryReceptions'   => $wineryReceptions,
            'declaredDeliveries' => $declaredDeliveries,
            'totalNotebook'      => $totalNotebook,
            'totalWinery'        => $totalWinery,
            'totalDeclared'      => $totalDeclared,
            'globalDiscrepancy'  => $globalDiscrepancy,
            'cupoKg'             => $cupoKg,
            'cupoPct'            => $cupoPct,
            'cupoExceeded'       => $cupoExceeded,
        ])->layout('layouts.app', [
            'title'       => 'Detalle de cosecha — ' . ($this->planting->grapeVariety?->name ?? $this->planting->name) . ' · ' . $this->vintageYear,
            'description' => 'Detalle de registros de cosecha, recepciones de bodega y entregas declaradas.',
        ]);
    }
}
