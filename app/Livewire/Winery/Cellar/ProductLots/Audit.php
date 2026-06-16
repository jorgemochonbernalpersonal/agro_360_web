<?php

namespace App\Livewire\Winery\Cellar\ProductLots;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\InvoiceStockMovement;
use App\Models\ProductLot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Audit extends Component
{
    use WithPagination, WithToastNotifications;

    public bool $onlyDrifted = false;

    // ─── Ajuste manual ────────────────────────────────────────────────────────
    public ?int $adjustingLotId = null;

    public string $adjustingLotName = '';

    public float $manualAvailable = 0;

    public float $manualReserved = 0;

    public float $manualSold = 0;

    public string $adjustmentNote = '';

    public function updatingOnlyDrifted(): void
    {
        $this->resetPage();
    }

    public function openAdjustModal(int $lotId): void
    {
        $lot = ProductLot::where('user_id', Auth::id())->findOrFail($lotId);

        $this->adjustingLotId = $lotId;
        $this->adjustingLotName = $lot->name;
        $this->manualAvailable = (float) $lot->available_quantity;
        $this->manualReserved = (float) $lot->reserved_quantity;
        $this->manualSold = (float) $lot->sold_quantity;
        $this->adjustmentNote = '';

        $this->dispatch('open-modal', name: 'manual-adjustment');
    }

    public function saveManualAdjustment(): void
    {
        $this->validate([
            'manualAvailable' => 'required|numeric|min:0',
            'manualReserved' => 'required|numeric|min:0',
            'manualSold' => 'required|numeric|min:0',
            'adjustmentNote' => 'required|string|min:5|max:500',
        ], [
            'adjustmentNote.required' => __('La justificación es obligatoria.'),
            'adjustmentNote.min' => __('La justificación debe tener al menos 5 caracteres.'),
        ]);

        $lot = ProductLot::where('user_id', Auth::id())->findOrFail($this->adjustingLotId);

        $prevNote = $lot->notes ? $lot->notes."\n\n" : '';
        $newNote = $prevNote.'['.now()->format('d/m/Y H:i').'] Ajuste manual: '.$this->adjustmentNote;

        $lot->update([
            'available_quantity' => $this->manualAvailable,
            'reserved_quantity' => $this->manualReserved,
            'sold_quantity' => $this->manualSold,
            'quantity' => $this->manualAvailable + $this->manualReserved + $this->manualSold,
            'notes' => $newNote,
        ]);

        $this->dispatch('close-modal', name: 'manual-adjustment');
        $this->reset(['adjustingLotId', 'adjustingLotName', 'manualAvailable', 'manualReserved', 'manualSold', 'adjustmentNote']);
        $this->toastSuccess("Stock de «{$lot->name}» ajustado manualmente.");
    }

    // ─── Reconstrucción de un lote ────────────────────────────────────────────

    public function recalculate(int $lotId): void
    {
        $lot = ProductLot::where('user_id', Auth::id())->findOrFail($lotId);

        DB::transaction(function () use ($lot) {
            $lot->lockForUpdate()->find($lot->id); // re-lock

            [$available, $reserved, $sold] = $this->reconstructFromMovements($lot->id, (float) $lot->initial_quantity);

            $lot->update([
                'available_quantity' => $available,
                'reserved_quantity' => $reserved,
                'sold_quantity' => $sold,
                'quantity' => $available + $reserved + $sold,
            ]);
        });

        $this->toastSuccess("Stock de «{$lot->name}» recalculado correctamente.");
    }

    // ─── Recalcular todos los lotes con drift ─────────────────────────────────

    public function recalculateAll(): void
    {
        $lots = ProductLot::where('user_id', Auth::id())->get();
        $fixed = 0;

        DB::transaction(function () use ($lots, &$fixed) {
            foreach ($lots as $lot) {
                if ($lot->isBalanced()) {
                    continue;
                }

                [$available, $reserved, $sold] = $this->reconstructFromMovements($lot->id, (float) $lot->initial_quantity);

                $lot->update([
                    'available_quantity' => $available,
                    'reserved_quantity' => $reserved,
                    'sold_quantity' => $sold,
                    'quantity' => $available + $reserved + $sold,
                ]);

                $fixed++;
            }
        });

        $this->toastSuccess("{$fixed} lote(s) recalculado(s) correctamente.");
    }

    // ─── Render ───────────────────────────────────────────────────────────────

    public function render()
    {
        $query = ProductLot::where('user_id', Auth::id())
            ->orderBy('name');

        $lots = $query->get();

        // Enriquecer con datos de auditoría
        $rows = $lots->map(function (ProductLot $lot) {
            [$expAvail, $expRes, $expSold] = $this->reconstructFromMovements($lot->id, (float) $lot->initial_quantity);

            $expTotal = $expAvail + $expRes + $expSold;

            $driftAvail = round((float) $lot->available_quantity - $expAvail, 3);
            $driftRes = round((float) $lot->reserved_quantity - $expRes, 3);
            $driftSold = round((float) $lot->sold_quantity - $expSold, 3);
            $driftTotal = round((float) $lot->quantity - $expTotal, 3);

            $hasDrift = abs($driftAvail) >= 0.001
                     || abs($driftRes) >= 0.001
                     || abs($driftSold) >= 0.001
                     || abs($driftTotal) >= 0.001;

            $movCount = InvoiceStockMovement::where('wine_lot_id', $lot->id)->count();

            return [
                'lot' => $lot,
                'expAvail' => $expAvail,
                'expRes' => $expRes,
                'expSold' => $expSold,
                'expTotal' => $expTotal,
                'driftAvail' => $driftAvail,
                'driftRes' => $driftRes,
                'driftSold' => $driftSold,
                'driftTotal' => $driftTotal,
                'hasDrift' => $hasDrift,
                'movCount' => $movCount,
            ];
        });

        $stats = [
            'total' => $lots->count(),
            'drifted' => $rows->filter(fn ($r) => $r['hasDrift'])->count(),
            'ok' => $rows->filter(fn ($r) => ! $r['hasDrift'])->count(),
        ];

        if ($this->onlyDrifted) {
            $rows = $rows->filter(fn ($r) => $r['hasDrift']);
        }

        return view('livewire.winery.cellar.product-lots.audit', [
            'rows' => $rows->values(),
            'stats' => $stats,
        ])->layout('layouts.app');
    }

    // ─── Helper: replay de movimientos ───────────────────────────────────────

    private function reconstructFromMovements(int $lotId, float $initialQty): array
    {
        $available = $initialQty;
        $reserved = 0.0;
        $sold = 0.0;

        $movements = InvoiceStockMovement::where('wine_lot_id', $lotId)
            ->orderBy('id')
            ->get(['qty', 'from_bucket', 'to_bucket']);

        foreach ($movements as $mov) {
            $qty = (float) $mov->qty;
            $from = $mov->from_bucket;
            $to = $mov->to_bucket;

            // Decrement source bucket
            match ($from) {
                'available' => $available -= $qty,
                'reserved' => $reserved -= $qty,
                'sold' => $sold -= $qty,
                default => null,
            };

            // Increment destination bucket
            match ($to) {
                'available' => $available += $qty,
                'reserved' => $reserved += $qty,
                'sold' => $sold += $qty,
                default => null,
            };
        }

        return [
            round(max(0.0, $available), 3),
            round(max(0.0, $reserved), 3),
            round(max(0.0, $sold), 3),
        ];
    }
}
