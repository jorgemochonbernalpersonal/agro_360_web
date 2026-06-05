<?php

namespace App\Livewire\Viticulturist\Billing\HarvestSale;

use App\Livewire\Concerns\WithHarvestSaleStock;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\HarvestStock;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MarketedHarvest;
use App\Models\ViticulturistSetting;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    use WithHarvestSaleStock, WithRoleAwareRedirect, WithToastNotifications;

    public Invoice $invoice;

    public string $buyer_name = '';

    public string $buyer_rega_code = '';

    public string $destination_type = 'third_party';

    public string $transport_document = '';

    public string $vehicle_plate = '';

    public string $delivery_date = '';

    public string $invoice_date = '';

    public string $payment_type = '';

    public string $observations = '';

    /** @var array<int, array{harvest_id:int, quantity:string, unit_price:string, tax_rate:string, description:string}> */
    public array $lines = [];

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount(int $id): void
    {
        $this->invoice = Invoice::where('user_id', Auth::id())
            ->where('invoice_type', 'harvest_sale')
            ->with(['items', 'items.marketedHarvest'])
            ->findOrFail($id);

        $this->invoice_date = $this->invoice->invoice_date?->format('Y-m-d') ?? '';
        $this->observations = $this->invoice->observations ?? '';
        $this->payment_type = $this->invoice->payment_type ?? '';

        // Load from first marketed harvest for buyer/regulatory fields
        $firstMh = MarketedHarvest::where('invoice_id', $this->invoice->id)->first();
        if ($firstMh) {
            $this->buyer_name = $firstMh->buyer_name ?? $this->invoice->billing_company_name ?? '';
            $this->buyer_rega_code = $firstMh->buyer_rega_code ?? '';
            $this->destination_type = $firstMh->destination_type ?? 'third_party';
            $this->transport_document = $firstMh->transport_document ?? '';
            $this->vehicle_plate = $firstMh->vehicle_plate ?? '';
            $this->delivery_date = $firstMh->delivery_date?->format('Y-m-d') ?? now()->toDateString();
        } else {
            $this->buyer_name = $this->invoice->billing_company_name ?? '';
            $this->delivery_date = now()->toDateString();
        }

        // Load lines from invoice items
        $this->lines = $this->invoice->items->map(function ($item) {
            return [
                'harvest_id' => $item->harvest_id,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'tax_rate' => (string) $item->tax_rate,
                'description' => $item->description ?? '',
            ];
        })->values()->toArray();
    }

    public function getIsLockedProperty(): bool
    {
        return $this->invoice->status === 'cancelled'
            || $this->invoice->payment_status === 'paid'
            || $this->invoice->delivery_status === 'delivered';
    }

    // ── Harvest toggle ─────────────────────────────────────────────────────────

    public function toggleHarvest(int $harvestId): void
    {
        $existing = array_search($harvestId, array_column($this->lines, 'harvest_id'));

        if ($existing !== false) {
            array_splice($this->lines, $existing, 1);
            $this->lines = array_values($this->lines);

            return;
        }

        $harvest = Harvest::whereHas('activity', fn ($q) => $q->where('viticulturist_id', Auth::id()))
            ->find($harvestId);
        if (! $harvest) {
            return;
        }

        $state = $this->getHarvestStockState($harvestId);
        $setting = ViticulturistSetting::forUser(Auth::id());

        $this->lines[] = [
            'harvest_id' => $harvestId,
            'quantity' => (string) round($state['available'], 3),
            'unit_price' => (string) ($harvest->price_per_kg ?? ''),
            'tax_rate' => (string) ($setting?->default_irpf_rate ?? '0'),
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
        $this->lines = array_values($this->lines);
    }

    // ── Save ───────────────────────────────────────────────────────────────────

    public function save()
    {
        if ($this->isLocked) {
            $this->toastError(__('Esta factura no se puede editar.'));

            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                // Lock invoice row to prevent concurrent edits
                Invoice::where('id', $this->invoice->id)->lockForUpdate()->first();

                // ── 1. Revert old stock reservations ───────────────────────────
                $oldItems = InvoiceItem::where('invoice_id', $this->invoice->id)->get();
                foreach ($oldItems as $oldItem) {
                    if ($oldItem->harvest_id && $oldItem->quantity > 0) {
                        $this->releaseHarvestStock(
                            $oldItem->harvest_id,
                            (float) $oldItem->quantity,
                            $this->invoice->id
                        );
                    }
                }

                // ── 2. Clear old MarketedHarvests + items ───────────────────────
                MarketedHarvest::where('invoice_id', $this->invoice->id)->delete();
                $this->invoice->items()->delete();

                // ── 3. Validate ownership ───────────────────────────────────────
                foreach ($this->lines as $line) {
                    $this->invoiceService->validateViticulturistHarvestOwnership(
                        (int) $line['harvest_id'],
                        Auth::id()
                    );
                }

                // ── 4. Recalculate totals ───────────────────────────────────────
                $totals = $this->invoiceService->calculateIrpfTotals($this->lines);

                // ── 5. Update invoice header ────────────────────────────────────
                $this->invoice->update([
                    'invoice_date' => $this->invoice_date,
                    'billing_company_name' => $this->buyer_name,
                    'subtotal' => $totals['subtotal'],
                    'tax_base' => $totals['tax_base'],
                    'tax_amount' => $totals['tax_amount'],
                    'total_amount' => $totals['total'],
                    'payment_type' => $this->payment_type ?: null,
                    'observations' => $this->observations ?: null,
                ]);

                // ── 6. Re-create items + MarketedHarvests + reserve ────────────
                foreach ($this->lines as $line) {
                    $harvest = Harvest::find($line['harvest_id']);
                    $qty = (float) $line['quantity'];
                    $unitPrice = (float) $line['unit_price'];
                    $taxRate = (float) $line['tax_rate'];
                    $subLine = round($qty * $unitPrice, 3);
                    $taxLine = round($subLine * ($taxRate / 100), 3);

                    $description = $line['description'] ?: sprintf(
                        'Cosecha #%d — %s',
                        $harvest->id,
                        $harvest->harvest_start_date?->format('d/m/Y') ?? '—'
                    );

                    $item = InvoiceItem::create([
                        'invoice_id' => $this->invoice->id,
                        'harvest_id' => $harvest->id,
                        'concept_type' => 'harvest',
                        'name' => $description,
                        'description' => $line['description'] ?: null,
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'tax_rate' => $taxRate,
                        'subtotal' => $subLine,
                        'tax_base' => $subLine,
                        'tax_amount' => $taxLine,
                        'total' => $subLine - $taxLine,
                        'delivery_status' => 'pending',
                    ]);

                    $mh = MarketedHarvest::create([
                        'harvest_id' => $harvest->id,
                        'campaign_id' => $harvest->activity?->campaign_id,
                        'viticulturist_id' => Auth::id(),
                        'delivery_date' => $this->delivery_date,
                        'quantity_kg' => $qty,
                        'destination_type' => $this->destination_type,
                        'buyer_name' => $this->buyer_name,
                        'buyer_rega_code' => $this->buyer_rega_code ?: null,
                        'transport_document' => $this->transport_document ?: null,
                        'vehicle_plate' => $this->vehicle_plate ?: null,
                        'price_per_kg' => $unitPrice,
                        'total_value' => round($qty * $unitPrice, 2),
                        'invoice_id' => $this->invoice->id,
                    ]);

                    $item->update(['marketed_harvest_id' => $mh->id]);
                }
            });

            $this->toastSuccess(__('Factura actualizada correctamente.'));

            return $this->viticulturistRoleRedirect('invoices.harvest-sale.index');

        } catch (\Exception $e) {
            Log::error('Error al editar factura de vendimia: '.$e->getMessage(), [
                'invoice_id' => $this->invoice->id,
                'user_id' => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al guardar los cambios.'));
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $viticulturistId = Auth::id();
        $selectedIds = array_column($this->lines, 'harvest_id');

        // Current invoice's harvest IDs — always available for re-selection
        $currentHarvestIds = $this->invoice->items->pluck('harvest_id')->filter()->toArray();

        $availableHarvests = Harvest::whereHas(
            'activity', fn ($q) => $q->where('viticulturist_id', $viticulturistId)
        )
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'cancelled');
            })
            ->where(function ($q) {
                $q->whereNull('disqualified')->orWhere('disqualified', false);
            })
            ->with(['activity.campaign:id,name', 'activity.plotPlanting.plotVariety.grapeVariety:id,name'])
            ->get()
            ->map(function ($harvest) use ($currentHarvestIds) {
                $latest = HarvestStock::where('harvest_id', $harvest->id)
                    ->whereNull('container_id')
                    ->latest()
                    ->first();

                $available = $latest
                    ? (float) $latest->available_qty
                    : (float) ($harvest->total_weight ?? 0);

                // Add back the currently reserved qty for this invoice's items
                if (in_array($harvest->id, $currentHarvestIds)) {
                    $alreadyReserved = $this->invoice->items
                        ->where('harvest_id', $harvest->id)
                        ->sum('quantity');
                    $available += (float) $alreadyReserved;
                }

                $harvest->available_qty = $available;
                $harvest->reserved_qty = $latest ? (float) $latest->reserved_qty : 0.0;
                $harvest->sold_qty = $latest ? (float) $latest->sold_qty : 0.0;

                return $harvest;
            })
            ->filter(fn ($h) => $h->available_qty > 0.001 || in_array($h->id, $currentHarvestIds))
            ->values();

        return view('livewire.viticulturist.billing.harvest-sale.edit', [
            'availableHarvests' => $availableHarvests,
            'selectedIds' => $selectedIds,
            'isLocked' => $this->isLocked,
        ])->layout('layouts.app');
    }

    // ── Validation ─────────────────────────────────────────────────────────────

    protected function rules(): array
    {
        return [
            'buyer_name' => 'required|string|max:255',
            'buyer_rega_code' => 'nullable|string|max:30',
            'destination_type' => 'required|in:own_winery,cooperative,third_party,other',
            'transport_document' => 'nullable|string|max:50',
            'vehicle_plate' => 'nullable|string|max:15',
            'delivery_date' => 'required|date',
            'invoice_date' => 'required|date',
            'payment_type' => 'nullable|in:cash,transfer,check,other',
            'observations' => 'nullable|string',
            'lines' => 'required|array|min:1',
            'lines.*.harvest_id' => 'required|exists:harvests,id',
            'lines.*.quantity' => 'required|numeric|min:0.001',
            'lines.*.unit_price' => 'required|numeric|min:0',
            'lines.*.tax_rate' => 'required|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:255',
        ];
    }
}
