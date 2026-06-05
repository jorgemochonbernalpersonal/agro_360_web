<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithRoleAwareRedirect, WithToastNotifications;

    public Invoice $invoice;

    public string $invoice_date = '';

    public string $observations = '';

    public string $payment_type = '';

    public array $lines = [];

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

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
                'harvest_id' => (int) $item->harvest_id,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'tax_rate' => (string) $item->tax_rate,
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
        if (! $harvest) {
            return;
        }

        $this->lines[] = [
            'harvest_id' => $harvestId,
            'quantity' => (string) ($harvest->total_weight ?? 0),
            'unit_price' => '',
            'tax_rate' => '0',
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    // ── Save ──────────────────────────────────────────────────────────────────

    public function save()
    {
        if ($this->isLocked) {
            $this->toastError(__('Esta liquidación no se puede editar.'));

            return;
        }

        $this->validate();

        $viticulturistId = $this->invoice->viticulturist_id;
        $wineryId = Auth::id();

        try {
            DB::transaction(function () use ($viticulturistId, $wineryId) {
                // ── 1. Calculate new totals
                $totals = $this->invoiceService->calculateIrpfTotals($this->lines);

                // ── 2. Update invoice header (numbers stay unchanged)
                $this->invoice->update([
                    'invoice_date' => $this->invoice_date,
                    'subtotal' => $totals['subtotal'],
                    'tax_base' => $totals['tax_base'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                ]);

                // ── 3. Delete existing items
                //       No stock to unreserve — GrapePurchase has no HarvestStock movements.
                //       InvoiceItemObserver.deleting also skips items without harvest stock tracking.
                $this->invoice->items()->delete();

                // ── 4. Create new items with ownership + double-invoicing guards
                foreach ($this->lines as $line) {
                    $harvest = $this->invoiceService->validateWineryHarvestOwnership(
                        (int) $line['harvest_id'],
                        $wineryId,
                        (int) $viticulturistId,
                        $this->invoice->id,
                    );

                    $qty = (float) $line['quantity'];
                    $unitPrice = (float) $line['unit_price'];
                    $taxRate = (float) $line['tax_rate'];
                    $subtotalLine = round($qty * $unitPrice, 3);
                    $taxAmountLine = round($subtotalLine * ($taxRate / 100), 3);

                    $variety = $harvest->plotPlanting?->grapeVariety?->name ?? 'uva';
                    $description = $line['description'] ?: "Vendimia #{$harvest->id} - {$variety}";

                    InvoiceItem::create([
                        'invoice_id' => $this->invoice->id,
                        'harvest_id' => $harvest->id,
                        'concept_type' => 'harvest',
                        'name' => $description,
                        'description' => $line['description'] ?: null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'subtotal' => $subtotalLine,
                        'tax_base' => $subtotalLine,
                        'tax_amount' => $taxAmountLine,
                        'total' => $subtotalLine - $taxAmountLine,
                    ]);
                }
            });

            $this->toastSuccess(__('Liquidación actualizada correctamente.'));

            return $this->roleRedirect('invoices.grape-purchase.index');

        } catch (\Exception $e) {
            Log::error('Error al editar liquidación de vendimia: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => $wineryId,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al guardar los cambios.'));
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render()
    {
        $wineryId = Auth::id();
        $viticulturistId = $this->invoice->viticulturist_id;
        $selectedIds = array_column($this->lines, 'harvest_id');

        // Available harvests = free + those already in THIS invoice
        $availableHarvests = Harvest::where('winery_id', $wineryId)
            ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
            ->where(function ($q) {
                $q->whereDoesntHave('invoiceItems', function ($q2) {
                    $q2->where('concept_type', 'harvest')
                        ->whereHas('invoice', fn ($q3) => $q3->where('status', '!=', 'cancelled')
                            ->where('id', '!=', $this->invoice->id)
                        );
                });
            })
            ->with(['plotPlanting.grapeVariety'])
            ->orderByDesc('harvest_start_date')
            ->get();

        return view('livewire.winery.billing.grape-purchase.edit', [
            'availableHarvests' => $availableHarvests,
            'selectedIds' => $selectedIds,
            'isLocked' => $this->isLocked,
        ])->layout('layouts.app');
    }

    // ── Validation ────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'invoice_date' => 'required|date',
            'payment_type' => 'nullable|in:cash,transfer,check,other',
            'observations' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.harvest_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wineryId = \Illuminate\Support\Facades\Auth::id();
                    $viticulturistId = $this->invoice->viticulturist_id;
                    if ($value && ! \App\Models\Harvest::where('id', $value)
                        ->where('winery_id', $wineryId)
                        ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $viticulturistId))
                        ->exists()) {
                        $fail(__('La recepción seleccionada no pertenece a esta liquidación.'));
                    }
                },
            ],
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'required|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:255',
        ];
    }

    protected function validationAttributes(): array
    {
        $attrs = ['invoice_date' => 'fecha'];
        foreach ($this->lines as $i => $_) {
            $attrs["lines.{$i}.quantity"] = 'kg';
            $attrs["lines.{$i}.unit_price"] = '€/kg';
            $attrs["lines.{$i}.tax_rate"] = 'retención';
        }

        return $attrs;
    }
}
