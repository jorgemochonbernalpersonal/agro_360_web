<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithGrapePurchaseFormRules;
use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\GrapePurchaseInvoiceIssuedNotification;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithGrapePurchaseFormRules, WithRoleAwareRedirect, WithToastNotifications;

    public string $viticulturist_id = '';

    public string $invoice_date = '';

    public string $observations = '';

    public string $payment_type = '';

    // Selected harvest rows
    public array $lines = [];

    protected InvoiceService $invoiceService;

    public function boot(InvoiceService $invoiceService): void
    {
        $this->invoiceService = $invoiceService;
    }

    public function mount(): void
    {
        $this->invoice_date = now()->toDateString();
    }

    public function updatedViticulturistId(): void
    {
        $this->lines = [];
    }

    public function toggleHarvest(int $harvestId): void
    {
        $existing = array_search($harvestId, array_column($this->lines, 'harvest_id'));

        if ($existing !== false) {
            array_splice($this->lines, $existing, 1);
            $this->lines = array_values($this->lines);

            return;
        }

        $harvest = Harvest::where('winery_id', Auth::id())->find($harvestId);
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

    public function save()
    {
        $this->validate();

        // Guard: viticulturist must belong to this winery (bypass for producer using own plots)
        $isSelfPurchase = Auth::user()->isProducer() && (int) $this->viticulturist_id === Auth::id();
        $belongs = $isSelfPurchase || WineryViticulturist::where('winery_id', Auth::id())
            ->where('viticulturist_id', $this->viticulturist_id)
            ->where('source', 'own')
            ->exists();

        if (! $belongs) {
            $this->addError('viticulturist_id', __('El viticultor no pertenece a tu bodega.'));

            return;
        }

        $viticulturist = User::findOrFail($this->viticulturist_id);

        try {
            DB::beginTransaction();

            // ── Atomic sequential numbering ───────────────────────────────────────────
            ['number' => $number, 'noteCode' => $noteCode] =
                $this->invoiceService->generateSequentialNumber(Auth::id(), 'grape_purchase_seq', 'GP-', 'LIQ-');

            // ── Calculate totals (IRPF: tax deducted from payment) ────────────────────
            $totals = $this->invoiceService->calculateIrpfTotals($this->lines);

            // ── Create invoice ────────────────────────────────────────────────────────
            $invoice = Invoice::create([
                'user_id' => Auth::id(),
                'invoice_type' => 'grape_purchase',
                'viticulturist_id' => $viticulturist->id,
                'invoice_number' => $number,
                'delivery_note_code' => $noteCode,
                'invoice_date' => $this->invoice_date,
                'billing_first_name' => $viticulturist->name,
                'billing_email' => $viticulturist->email,
                'subtotal' => $totals['subtotal'],
                'tax_base' => $totals['tax_base'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total'],
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'payment_type' => $this->payment_type ?: null,
                'observations' => $this->observations ?: null,
            ]);

            // ── Create items — guard against double-invoicing per harvest ─────────────
            foreach ($this->lines as $line) {
                $harvest = $this->invoiceService->validateWineryHarvestOwnership(
                    (int) $line['harvest_id'],
                    Auth::id(),
                    (int) $this->viticulturist_id,
                );

                $amounts = $this->invoiceService->calculateIrpfLine($line);
                $qty = $amounts['quantity'];
                $unitPrice = $amounts['unit_price'];

                $variety = $harvest->plotPlanting?->grapeVariety?->name ?? 'uva';
                $description = $line['description'] ?: "Vendimia #{$harvest->id} - {$variety}";

                InvoiceItem::create([
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
                ]);
            }

            DB::commit();

            // Notify viticulturist
            $invoice->load('user');
            $viticulturist->notify(new GrapePurchaseInvoiceIssuedNotification($invoice));

            $this->toastSuccess("Liquidación {$number} creada — Ref.: {$noteCode}");

            return $this->roleRedirect('invoices.grape-purchase.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear liquidación de vendimia: '.$e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : __('Error al crear la liquidación. Inténtalo de nuevo.'));
        }
    }

    public function render()
    {
        $wineryId = Auth::id();

        $viticulturistIds = WineryViticulturist::where('winery_id', $wineryId)
            ->where('source', 'own')
            ->pluck('viticulturist_id');

        if (Auth::user()->isProducer()) {
            $viticulturistIds = $viticulturistIds->push($wineryId)->unique();
        }

        $viticulturists = User::whereIn('id', $viticulturistIds)->orderBy('name')->get(['id', 'name']);

        $availableHarvests = collect();
        $selectedHarvestIds = array_column($this->lines, 'harvest_id');

        if ($this->viticulturist_id) {
            $availableHarvests = Harvest::where('winery_id', $wineryId)
                ->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $this->viticulturist_id))
                // Exclude harvests already included in an active (non-cancelled) invoice
                ->whereDoesntHave('invoiceItems', function ($q) {
                    $q->where('concept_type', 'harvest')
                        ->whereHas('invoice', fn ($q2) => $q2->where('status', '!=', 'cancelled'));
                })
                ->with(['plotPlanting.grapeVariety'])
                ->orderByDesc('harvest_start_date')
                ->get();
        }

        return view('livewire.winery.billing.grape-purchase.create', [
            'viticulturists' => $viticulturists,
            'availableHarvests' => $availableHarvests,
            'selectedHarvestIds' => $selectedHarvestIds,
        ])->layout('layouts.app');
    }

    protected function rules(): array
    {
        return array_merge($this->grapePurchaseBaseRules(), [
            'viticulturist_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wineryId = \Illuminate\Support\Facades\Auth::id();
                    $isSelf = \Illuminate\Support\Facades\Auth::user()->isProducer() && (int) $value === $wineryId;
                    if (! $isSelf && $value && ! \App\Models\WineryViticulturist::where('winery_id', $wineryId)
                        ->where('viticulturist_id', $value)
                        ->where('source', 'own')
                        ->exists()) {
                        $fail(__('El viticultor seleccionado no pertenece a tu bodega.'));
                    }
                },
            ],
            'lines.*.harvest_id' => 'required|exists:harvests,id',
        ]);
    }
}
