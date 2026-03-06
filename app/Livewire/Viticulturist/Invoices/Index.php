<?php

namespace App\Livewire\Viticulturist\Invoices;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Concerns\WithToastNotifications;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Services\ContainerStockService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, WithToastNotifications, WithInvoiceActions;

    public string $search              = '';
    public string $filterStatus        = '';
    public string $filterPaymentStatus = '';

    // Modal emitir factura
    public bool   $emitirModal     = false;
    public ?int   $emitirInvoiceId = null;
    public string $emitirDate      = '';

    // Modal rectificativa
    public bool   $correctiveModal    = false;
    public ?int   $correctiveId       = null;
    public string $correctiveDate     = '';
    public string $correctiveReason   = '';

    protected $queryString = [
        'search'              => ['except' => ''],
        'filterStatus'        => ['except' => ''],
        'filterPaymentStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void              { $this->resetPage(); }
    public function updatingFilterStatus(): void        { $this->resetPage(); }
    public function updatingFilterPaymentStatus(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search              = '';
        $this->filterStatus        = '';
        $this->filterPaymentStatus = '';
        $this->resetPage();
    }

    // ── Emitir factura (draft → sent) ─────────────────────────────────────────

    public function openEmitirModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (!$invoice) return;

        if ($invoice->status !== 'draft') {
            $this->toastError('Solo se puede emitir una factura en estado borrador.');
            return;
        }

        $this->emitirInvoiceId = $id;
        $this->emitirDate      = now()->toDateString();
        $this->emitirModal     = true;
    }

    public function closeEmitirModal(): void
    {
        $this->emitirModal     = false;
        $this->emitirInvoiceId = null;
        $this->emitirDate      = '';
        $this->resetValidation();
    }

    public function confirmEmitir(): void
    {
        $this->validate(
            ['emitirDate' => 'required|date'],
            ['emitirDate.required' => 'La fecha de factura es obligatoria.']
        );

        $invoice = $this->findInvoice($this->emitirInvoiceId);
        if (!$invoice) return;

        if ($invoice->status !== 'draft') {
            $this->toastError('Esta factura ya no está en borrador.');
            $this->closeEmitirModal();
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                $settings      = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                // InvoiceObserver.updated detecta draft→sent y llama confirmSale() + billing snapshot
                $invoice->update([
                    'status'         => 'sent',
                    'invoice_date'   => $this->emitirDate,
                    'invoice_number' => $invoiceNumber,
                ]);
            });

            $this->closeEmitirModal();
            $this->toastSuccess('Factura emitida correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al emitir factura: ' . $e->getMessage(), ['invoice_id' => $this->emitirInvoiceId]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al emitir la factura.');
        }
    }

    // ── Factura Rectificativa ─────────────────────────────────────────────────

    public function openCorrectiveModal(int $id): void
    {
        $invoice = $this->findInvoice($id, ['items.harvest']);
        if (!$invoice) return;

        if ($invoice->status !== 'sent') {
            $this->toastError('Solo se puede rectificar una factura emitida.');
            return;
        }

        if ($invoice->corrective) {
            $this->toastError('Una rectificativa no puede rectificarse a sí misma.');
            return;
        }

        if (Invoice::where('corrected_invoice_id', $id)->exists()) {
            $this->toastError('Esta factura ya tiene una rectificativa asociada.');
            return;
        }

        $this->correctiveId     = $id;
        $this->correctiveDate   = now()->toDateString();
        $this->correctiveReason = '';
        $this->correctiveModal  = true;
    }

    public function closeCorrectiveModal(): void
    {
        $this->correctiveModal  = false;
        $this->correctiveId     = null;
        $this->correctiveDate   = '';
        $this->correctiveReason = '';
        $this->resetValidation();
    }

    public function confirmCorrective(): void
    {
        $this->validate(
            [
                'correctiveDate'   => 'required|date',
                'correctiveReason' => 'nullable|string|max:500',
            ],
            ['correctiveDate.required' => 'La fecha de la rectificativa es obligatoria.']
        );

        $original = $this->findInvoice($this->correctiveId, ['items.harvest']);
        if (!$original || $original->status !== 'sent') {
            $this->toastError('La factura original ya no es válida para rectificar.');
            $this->closeCorrectiveModal();
            return;
        }

        if (Invoice::where('corrected_invoice_id', $original->id)->exists()) {
            $this->toastError('Esta factura ya tiene una rectificativa asociada.');
            $this->closeCorrectiveModal();
            return;
        }

        $invoiceNumber = null;

        try {
            DB::transaction(function () use ($original, &$invoiceNumber) {
                // ── 1. Generate corrective invoice number atomically
                $settings      = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $notes = 'Rectificativa de ' . $original->invoice_number . '.'
                    . ($this->correctiveReason ? ' Motivo: ' . $this->correctiveReason : '');

                // ── 2. Create corrective invoice
                //       withoutEvents: we control the billing snapshot (use original's data)
                //       and avoid InvoiceObserver from touching stock or ordering fields.
                $corrective = Invoice::withoutEvents(fn () => Invoice::create([
                    'user_id'              => Auth::id(),
                    'client_id'            => $original->client_id,
                    'client_address_id'    => $original->client_address_id,
                    'corrected_invoice_id' => $original->id,
                    'invoice_type'         => $original->invoice_type,
                    'corrective'           => true,
                    'invoice_number'       => $invoiceNumber,
                    'invoice_date'         => $this->correctiveDate,
                    'order_date'           => now(),
                    'status'               => 'sent',
                    'payment_status'       => 'unpaid',
                    'delivery_status'      => 'cancelled',
                    // Negative amounts mirror original
                    'subtotal'             => -abs((float) $original->subtotal),
                    'discount_amount'      => -abs((float) $original->discount_amount),
                    'tax_base'             => -abs((float) $original->tax_base),
                    'tax_rate'             => $original->tax_rate,
                    'tax_amount'           => -abs((float) $original->tax_amount),
                    'total_amount'         => -abs((float) $original->total_amount),
                    // Billing snapshot frozen from original invoice
                    'billing_first_name'       => $original->billing_first_name,
                    'billing_last_name'        => $original->billing_last_name,
                    'billing_email'            => $original->billing_email,
                    'billing_phone'            => $original->billing_phone,
                    'billing_company_name'     => $original->billing_company_name,
                    'billing_company_document' => $original->billing_company_document,
                    'billing_address'          => $original->billing_address,
                    'billing_postal_code'      => $original->billing_postal_code,
                    'billing_city'             => $original->billing_city,
                    'billing_state'            => $original->billing_state,
                    'billing_country'          => $original->billing_country,
                    'observations'             => $notes,
                ]));

                // ── 3. Clone items with negative quantities
                //       withoutObservers: InvoiceItemObserver would call ContainerStockService
                //       which we handle explicitly below to avoid double movement.
                InvoiceItem::withoutObservers(function () use ($original, $corrective) {
                    foreach ($original->items as $item) {
                        $corrective->items()->create([
                            'harvest_id'          => $item->harvest_id,
                            'wine_lot_id'         => $item->wine_lot_id,
                            'concept_type'        => $item->concept_type,
                            'name'                => $item->name,
                            'description'         => $item->description,
                            'sku'                 => $item->sku,
                            'quantity'            => -(float) $item->quantity,
                            'unit_price'          => $item->unit_price,
                            'discount_percentage' => $item->discount_percentage,
                            'discount_amount'     => -(float) $item->discount_amount,
                            'tax_id'              => $item->tax_id,
                            'tax_name'            => $item->tax_name,
                            'tax_rate'            => $item->tax_rate,
                            'tax_base'            => -(float) $item->tax_base,
                            'tax_amount'          => -(float) $item->tax_amount,
                            'subtotal'            => -(float) $item->subtotal,
                            'total'               => -(float) $item->total,
                        ]);
                    }
                });

                // ── 4. Restore stock: original was 'sent' → harvest items are in sold bucket
                //       ContainerStockService.releaseFromInvoice(fromStatus='sent'):
                //         sold_qty -= qty, available_qty += qty, container.used_capacity += qty
                $stockService = app(ContainerStockService::class);
                foreach ($original->items as $item) {
                    if ($item->harvest_id && $item->harvest) {
                        $stockService->releaseFromInvoice($item->harvest, $item, 'sent');
                    }
                }
            });

            $this->closeCorrectiveModal();
            $this->toastSuccess("Rectificativa {$invoiceNumber} emitida. Stock restaurado.");

        } catch (\Exception $e) {
            Log::error('Error al crear factura rectificativa: ' . $e->getMessage(), [
                'original_invoice_id' => $this->correctiveId,
                'user_id'             => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al generar la rectificativa.');
        }
    }

    // ── Cancelar (solo draft) ─────────────────────────────────────────────────

    public function cancel(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (!$invoice) return;

        if ($invoice->status !== 'draft') {
            $this->toastError('Las facturas emitidas no se pueden cancelar directamente. Crea una factura rectificativa.');
            return;
        }

        try {
            // Only update status so InvoiceObserver.updated fires handleStatusChange
            // → releaseAllStock(). Updating both status+delivery_status simultaneously
            // would hit the delivery_status branch first (elseif) and skip stock release.
            $invoice->update(['status' => 'cancelled']);
            $invoice->updateQuietly(['delivery_status' => 'cancelled']); // silent, no observer
            $this->toastSuccess('Factura cancelada y stock liberado.');

        } catch (\Exception $e) {
            Log::error('Error al cancelar factura: ' . $e->getMessage(), ['invoice_id' => $id]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al cancelar la factura.');
        }
    }

    // ── Render ────────────────────────────────────────────────────────────────

    #[Layout('layouts.app', [
        'title'       => 'Facturas / Pedidos - Agro365',
        'description' => 'Gestiona tus facturas y pedidos.',
    ])]
    public function render()
    {
        $user = Auth::user();

        $query = Invoice::forUser($user->id)
            ->with(['client', 'items'])
            ->withCount('correctives')
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus) {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('invoice_number', 'like', '%' . $this->search . '%')
                  ->orWhere('delivery_note_code', 'like', '%' . $this->search . '%')
                  ->orWhereHas('client', function ($subQ) {
                      $subQ->where('first_name', 'like', '%' . $this->search . '%')
                           ->orWhere('last_name', 'like', '%' . $this->search . '%')
                           ->orWhere('company_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        $invoices = $query->paginate(12);

        return view('livewire.viticulturist.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::forUser(Auth::id())
            ->with($with)
            ->find($id);
    }
}
