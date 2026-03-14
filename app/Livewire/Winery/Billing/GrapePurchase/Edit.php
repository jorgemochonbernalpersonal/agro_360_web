<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\WineryViticulturist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithToastNotifications;

    public Invoice $invoice;

    public string $invoice_date = '';
    public string $observations = '';
    public string $payment_type = '';

    public array $lines = [];

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'grape_purchase')
            ->with('items')
            ->findOrFail($id);

        $this->invoice_date = $this->invoice->invoice_date
            ? $this->invoice->invoice_date->format('Y-m-d')
            : '';
        $this->observations = $this->invoice->observations ?? '';
        $this->payment_type = $this->invoice->payment_type ?? '';

        $this->lines = $this->invoice->items
            ->filter(fn ($item) => $item->harvest_id)
            ->map(fn ($item) => [
                'harvest_id'  => (int) $item->harvest_id,
                'quantity'    => (string) $item->quantity,
                'unit_price'  => (string) $item->unit_price,
                'tax_rate'    => (string) $item->tax_rate,
                'description' => $item->description ?? '',
            ])
            ->values()
            ->toArray();
    }

    public function getIsLockedProperty(): bool
    {
        return $this->invoice->status === 'cancelled'
            || $this->invoice->payment_status === 'paid';
    }

    // ── Harvest selection ─────────────────────────────────────────────────────

    /**
     * Toggle a harvest in/out of the lines array.
     * Pre-populates quantity from net/gross weight, price left blank for user input.
     */
    public function toggleHarvest(int $harvestId): void
    {
        $existing = array_search($harvestId, array_column($this->lines, 'harvest_id'));

        if ($existing !== false) {
            array_splice($this->lines, $existing, 1);
            $this->lines = array_values($this->lines);
            return;
        }

        $harvest = Harvest::find($harvestId);
        if (!$harvest) return;

        $this->lines[] = [
            'harvest_id'  => $harvestId,
            'quantity'    => (string) ($harvest->total_weight ?? 0),
            'unit_price'  => '',
            'tax_rate'    => '0',
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'invoice_date'        => 'required|date',
            'payment_type'        => 'nullable|in:cash,transfer,check,other',
            'observations'        => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.harvest_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wineryId        = \Illuminate\Support\Facades\Auth::id();
                    $viticulturistId = $this->invoice->viticulturist_id;
                    if ($value && !\App\Models\Harvest::where('id', $value)
                        ->where('winery_id', $wineryId)
                        ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
                        ->exists()) {
                        $fail('La recepción seleccionada no pertenece a esta liquidación.');
                    }
                },
            ],
            'lines.*.quantity'    => 'required|numeric|min:0.001',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'required|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:255',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = ['invoice_date' => 'fecha'];
        foreach ($this->lines as $i => $_) {
            $attrs["lines.{$i}.quantity"]   = 'kg';
            $attrs["lines.{$i}.unit_price"] = '€/kg';
            $attrs["lines.{$i}.tax_rate"]   = 'retención';
        }
        return $attrs;
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        if ($this->isLocked) {
            $this->toastError('Esta liquidación no se puede editar.');
            return;
        }

        $this->validate();

        $viticulturistId = $this->invoice->viticulturist_id;
        $wineryId        = Auth::id();

        try {
            DB::transaction(function () use ($viticulturistId, $wineryId) {
                // ── 1. Calculate new totals
                $subtotal  = 0;
                $taxAmount = 0;

                foreach ($this->lines as $line) {
                    $lineSubtotal  = (float) $line['quantity'] * (float) $line['unit_price'];
                    $subtotal     += $lineSubtotal;
                    $taxAmount    += $lineSubtotal * ((float) $line['tax_rate'] / 100);
                }

                $total = $subtotal - $taxAmount;

                // ── 2. Update invoice header (numbers stay unchanged)
                $this->invoice->update([
                    'invoice_date' => $this->invoice_date,
                    'subtotal'     => round($subtotal, 3),
                    'tax_base'     => round($subtotal, 3),
                    'tax_amount'   => round($taxAmount, 3),
                    'total_amount' => round($total, 3),
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                ]);

                // ── 3. Delete existing items
                //       No stock to unreserve — GrapePurchase has no HarvestStock movements.
                //       InvoiceItemObserver.deleting also skips items without harvest stock tracking.
                $this->invoice->items()->delete();

                // ── 4. Create new items with ownership + double-invoicing guards
                foreach ($this->lines as $line) {
                    $harvest = Harvest::lockForUpdate()->find($line['harvest_id']);

                    if (!$harvest || $harvest->winery_id !== $wineryId) {
                        throw new \RuntimeException(
                            "La recepción #{$line['harvest_id']} no pertenece a esta bodega."
                        );
                    }

                    $harvest->loadMissing('batch');
                    if ((string) $harvest->batch?->viticulturist_id !== (string) $viticulturistId) {
                        throw new \RuntimeException(
                            "La recepción #{$harvest->id} no pertenece al viticultor de esta liquidación."
                        );
                    }

                    // Double-invoicing guard: exclude THIS invoice from the check
                    if ($harvest->invoiceItems()
                            ->where('concept_type', 'harvest')
                            ->whereHas('invoice', fn ($q) =>
                                $q->where('status', '!=', 'cancelled')
                                  ->where('id', '!=', $this->invoice->id)
                            )
                            ->exists()) {
                        throw new \RuntimeException(
                            "La recepción #{$harvest->id} ya está incluida en otra liquidación activa."
                        );
                    }

                    $qty           = (float) $line['quantity'];
                    $unitPrice     = (float) $line['unit_price'];
                    $taxRate       = (float) $line['tax_rate'];
                    $subtotalLine  = round($qty * $unitPrice, 3);
                    $taxAmountLine = round($subtotalLine * ($taxRate / 100), 3);

                    $variety     = $harvest->plotPlanting?->grapeVariety?->name ?? 'uva';
                    $description = $line['description'] ?: "Vendimia #{$harvest->id} - {$variety}";

                    InvoiceItem::create([
                        'invoice_id'   => $this->invoice->id,
                        'harvest_id'   => $harvest->id,
                        'concept_type' => 'harvest',
                        'name'         => $description,
                        'description'  => $line['description'] ?: null,
                        'quantity'     => $qty,
                        'unit_price'   => $unitPrice,
                        'tax_rate'     => $taxRate,
                        'subtotal'     => $subtotalLine,
                        'tax_base'     => $subtotalLine,
                        'tax_amount'   => $taxAmountLine,
                        'total'        => $subtotalLine - $taxAmountLine,
                    ]);
                }
            });

            $this->toastSuccess('Liquidación actualizada correctamente.');
            return $this->redirect(route('winery.invoices.grape-purchase.index'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error al editar liquidación de vendimia: ' . $e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id'    => $wineryId,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al guardar los cambios.');
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $wineryId        = Auth::id();
        $viticulturistId = $this->invoice->viticulturist_id;
        $selectedIds     = array_column($this->lines, 'harvest_id');

        // Available harvests = free + those already in THIS invoice
        $availableHarvests = Harvest::where('winery_id', $wineryId)
            ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where(function ($q) {
                $q->whereDoesntHave('invoiceItems', function ($q2) {
                    $q2->where('concept_type', 'harvest')
                       ->whereHas('invoice', fn ($q3) =>
                           $q3->where('status', '!=', 'cancelled')
                              ->where('id', '!=', $this->invoice->id)
                       );
                });
            })
            ->with(['plotPlanting.grapeVariety'])
            ->orderByDesc('harvest_start_date')
            ->get();

        return view('livewire.winery.billing.grape-purchase.edit', [
            'availableHarvests' => $availableHarvests,
            'selectedIds'       => $selectedIds,
            'isLocked'          => $this->isLocked,
        ])->layout('layouts.app');
    }
}
