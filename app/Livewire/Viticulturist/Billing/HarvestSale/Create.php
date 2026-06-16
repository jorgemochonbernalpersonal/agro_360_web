<?php

namespace App\Livewire\Viticulturist\Billing\HarvestSale;

use App\Livewire\Concerns\WithHarvestSaleFormRules;
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

class Create extends Component
{
    use WithHarvestSaleFormRules, WithHarvestSaleStock, WithRoleAwareRedirect, WithToastNotifications;

    // ── Invoice header ─────────────────────────────────────────────────────────
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

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
        $this->delivery_date = now()->toDateString();

    }

    // ── Harvest toggle ─────────────────────────────────────────────────────────

    public function toggleHarvest(int $harvestId): void
    {
        $existing = array_search($harvestId, array_column($this->lines, 'harvest_id'));

        if ($existing !== false) {
            array_splice($this->lines, $existing, 1);

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
            'tax_rate' => (string) ($setting->default_irpf_rate ?? '0'),
            'description' => '',
        ];
    }

    public function removeLine(int $index): void
    {
        array_splice($this->lines, $index, 1);
    }

    // ── Save ───────────────────────────────────────────────────────────────────

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            // ── 1. Atomic sequential numbering ─────────────────────────────────
            ['number' => $number, 'noteCode' => $noteCode] =
                $this->invoiceService->generateSequentialNumber(Auth::id(), 'harvest_sale_seq', 'HS-', 'VEN-');

            // ── 2. Validate ownership + calculate totals ────────────────────────
            foreach ($this->lines as $line) {
                $this->invoiceService->validateViticulturistHarvestOwnership(
                    (int) $line['harvest_id'],
                    Auth::id()
                );
            }

            $totals = $this->invoiceService->calculateIrpfTotals($this->lines);

            // ── 3. Create invoice ───────────────────────────────────────────────
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'invoice_type' => 'harvest_sale',
                'invoice_number' => $number,
                'delivery_note_code' => $noteCode,
                'invoice_date' => $this->invoice_date,
                'billing_company_name' => $this->buyer_name,
                'subtotal' => $totals['subtotal'],
                'tax_base' => $totals['tax_base'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total'],
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'delivery_status' => 'pending',
                'payment_type' => $this->payment_type ?: null,
                'observations' => $this->observations ?: null,
            ]);

            // ── 4. Create items + MarketedHarvest + reserve stock ───────────────
            foreach ($this->lines as $line) {
                $harvest = Harvest::find($line['harvest_id']);

                $amounts = $this->invoiceService->calculateIrpfLine($line);
                $qty = $amounts['quantity'];
                $unitPrice = $amounts['unit_price'];

                $description = $line['description'] ?: sprintf(
                    'Cosecha #%d — %s',
                    $harvest->id,
                    $harvest->harvest_start_date->format('d/m/Y')
                );

                // Reserve stock (available → reserved)
                $this->reserveHarvestStock($harvest->id, $qty, $invoice->id);

                // Create invoice item
                $item = InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'harvest_id' => $harvest->id,
                    'concept_type' => 'harvest',
                    'name' => $description,
                    'description' => $line['description'] ?: null,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $amounts['tax_rate'],
                    'subtotal' => $amounts['subtotal'],
                    'tax_base' => $amounts['tax_base'],
                    'tax_amount' => $amounts['tax_amount'],
                    'total' => $amounts['total'],
                    'delivery_status' => 'pending',
                ]);

                // Create MarketedHarvest (regulatory albarán data)
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
                    'invoice_id' => $invoice->id,
                ]);

                // Link marketed_harvest_id on the item
                $item->update(['marketed_harvest_id' => $mh->id]);
            }

            DB::commit();

            $this->toastSuccess("Factura {$number} creada — Ref.: {$noteCode}");

            return $this->viticulturistRoleRedirect('invoices.harvest-sale.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear factura de vendimia: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al crear la factura. Inténtalo de nuevo.'));
        }
    }

    // ── Render ─────────────────────────────────────────────────────────────────

    public function render()
    {
        $viticulturistId = Auth::id();
        $selectedIds = array_column($this->lines, 'harvest_id');

        // Load harvests with available stock
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
            ->map(function ($harvest) {
                $latest = HarvestStock::where('harvest_id', $harvest->id)
                    ->whereNull('container_id')
                    ->latest()
                    ->first();

                $harvest->available_qty = $latest
                    ? (float) $latest->available_qty
                    : (float) ($harvest->total_weight ?? 0);
                $harvest->reserved_qty = $latest ? (float) $latest->reserved_qty : 0.0;
                $harvest->sold_qty = $latest ? (float) $latest->sold_qty : 0.0;

                return $harvest;
            })
            ->filter(fn ($h) => $h->available_qty > 0.001)
            ->values();

        // Buyers from past harvest_sale invoices
        $buyers = Invoice::where('user_id', $viticulturistId)
            ->where('invoice_type', 'harvest_sale')
            ->whereNotNull('billing_company_name')
            ->distinct()
            ->orderBy('billing_company_name')
            ->pluck('billing_company_name');

        return view('livewire.viticulturist.billing.harvest-sale.create', [
            'availableHarvests' => $availableHarvests,
            'selectedIds' => $selectedIds,
            'buyers' => $buyers,
        ])->layout('layouts.app');
    }

}
