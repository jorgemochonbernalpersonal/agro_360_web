<?php

namespace App\Livewire\Winery\Billing\GrapePurchase;

use App\Livewire\Concerns\WithRoleAwareRedirect;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\User;
use App\Models\WineryViticulturist;
use App\Notifications\GrapePurchaseInvoiceIssuedNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    use WithToastNotifications, WithRoleAwareRedirect;

    public string $viticulturist_id = '';
    public string $invoice_date     = '';
    public string $observations     = '';
    public string $payment_type     = '';

    // Selected harvest rows
    public array $lines = [];

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

    protected function rules(): array
    {
        return [
            'viticulturist_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $wineryId   = \Illuminate\Support\Facades\Auth::id();
                    $isSelf     = \Illuminate\Support\Facades\Auth::user()->isProducer() && (int) $value === $wineryId;
                    if (!$isSelf && $value && !\App\Models\WineryViticulturist::where('winery_id', $wineryId)
                        ->where('viticulturist_id', $value)
                        ->where('source', 'own')
                        ->exists()) {
                        $fail(__('El viticultor seleccionado no pertenece a tu bodega.'));
                    }
                },
            ],
            'invoice_date'        => 'required|date',
            'payment_type'        => 'nullable|in:cash,transfer,check,other',
            'observations'        => 'nullable|string',
            'lines'               => 'required|array|min:1',
            'lines.*.harvest_id'  => 'required|exists:harvests,id',
            'lines.*.quantity'    => 'required|numeric|min:0.001',
            'lines.*.unit_price'  => 'required|numeric|min:0',
            'lines.*.tax_rate'    => 'required|numeric|min:0|max:100',
            'lines.*.description' => 'nullable|string|max:255',
        ];
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

        if (!$belongs) {
            $this->addError('viticulturist_id', __('El viticultor no pertenece a tu bodega.'));
            return;
        }

        $viticulturist = User::findOrFail($this->viticulturist_id);

        try {
            DB::beginTransaction();

            // ── Atomic sequential numbering ───────────────────────────────────────────
            DB::table('users')->where('id', Auth::id())->lockForUpdate()->first();
            DB::table('users')->where('id', Auth::id())->increment('grape_purchase_seq');
            $seq  = DB::table('users')->where('id', Auth::id())->value('grape_purchase_seq');
            $year = now()->year;

            $number   = 'GP-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
            $noteCode = 'LIQ-' . $year . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // ── Calculate totals ──────────────────────────────────────────────────────
            $subtotal  = 0;
            $taxAmount = 0;

            foreach ($this->lines as $line) {
                $lineSubtotal  = (float) $line['quantity'] * (float) $line['unit_price'];
                $lineTax       = $lineSubtotal * ((float) $line['tax_rate'] / 100);
                $subtotal     += $lineSubtotal;
                $taxAmount    += $lineTax;
            }

            // tax_rate en compra de uva = retención IRPF (se resta del pago al viticultor)
            $total = $subtotal - $taxAmount;

            // ── Create invoice ────────────────────────────────────────────────────────
            $invoice = Invoice::create([
                'user_id'               => Auth::id(),
                'invoice_type'          => 'grape_purchase',
                'viticulturist_id'      => $viticulturist->id,
                'invoice_number'        => $number,
                'delivery_note_code'    => $noteCode,
                'invoice_date'          => $this->invoice_date,
                'billing_first_name'    => $viticulturist->name,
                'billing_email'         => $viticulturist->email,
                'subtotal'              => round($subtotal, 3),
                'tax_base'              => round($subtotal, 3),
                'tax_amount'            => round($taxAmount, 3),
                'total_amount'          => round($total, 3),
                'status'                => 'draft',
                'payment_status'        => 'unpaid',
                'payment_type'          => $this->payment_type ?: null,
                'observations'          => $this->observations ?: null,
            ]);

            // ── Create items — guard against double-invoicing per harvest ─────────────
            foreach ($this->lines as $line) {
                $harvest = Harvest::lockForUpdate()->find($line['harvest_id']);

                // Ownership guard: harvest must belong to this winery
                if (!$harvest || $harvest->winery_id !== Auth::id()) {
                    throw new \RuntimeException(
                        "La recepción #{$line['harvest_id']} no pertenece a esta bodega."
                    );
                }

                // Ownership guard: harvest batch must belong to the selected viticulturist
                $harvest->loadMissing('batch');
                $harvestViticulturistId = $harvest->batch?->viticulturist_id;
                $isSelfOrMatches = $isSelfPurchase || (string) $harvestViticulturistId === (string) $this->viticulturist_id;

                if (!$isSelfOrMatches) {
                    throw new \RuntimeException(
                        "La recepción #{$harvest->id} no pertenece al viticultor seleccionado."
                    );
                }

                // Double-guard: harvest must not be included in any active (non-cancelled) invoice
                // Lock invoice_items to prevent race condition on concurrent requests
                if ($harvest->invoiceItems()
                        ->lockForUpdate()
                        ->where('concept_type', 'harvest')
                        ->whereHas('invoice', fn($q) => $q->where('status', '!=', 'cancelled'))
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
                    'invoice_id'   => $invoice->id,
                    'harvest_id'   => $harvest?->id,
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

            DB::commit();

            // Notify viticulturist
            $invoice->load('user');
            $viticulturist->notify(new GrapePurchaseInvoiceIssuedNotification($invoice));

            $this->toastSuccess("Liquidación {$number} creada — Ref.: {$noteCode}");
            return $this->roleRedirect('invoices.grape-purchase.index');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear liquidación de vendimia: ' . $e->getMessage(), [
                'user_id'   => Auth::id(),
                'exception' => $e,
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage()  : __('Error al crear la liquidación. Inténtalo de nuevo.'));
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
            'viticulturists'     => $viticulturists,
            'availableHarvests'  => $availableHarvests,
            'selectedHarvestIds' => $selectedHarvestIds,
        ])->layout('layouts.app');
    }
}
