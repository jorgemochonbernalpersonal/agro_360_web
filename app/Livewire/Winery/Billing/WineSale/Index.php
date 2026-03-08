<?php

namespace App\Livewire\Winery\Billing\WineSale;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Winery\AbstractIndex;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Services\WineStockService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Index extends AbstractIndex
{
    use WithInvoiceActions;

    public string $search               = '';
    public string $filterStatus         = '';
    public string $filterPaymentStatus  = '';
    public string $filterDeliveryStatus = '';

    // Modal emitir
    public bool   $emitirModal = false;
    public ?int   $emitirId    = null;
    public string $emitirDate  = '';

    // Modal rectificativa
    public bool   $correctiveModal  = false;
    public ?int   $correctiveId     = null;
    public string $correctiveDate   = '';
    public string $correctiveReason = '';

    protected $queryString = [
        'search'               => ['except' => ''],
        'filterStatus'         => ['except' => ''],
        'filterPaymentStatus'  => ['except' => ''],
        'filterDeliveryStatus' => ['except' => ''],
    ];

    public function updatingSearch(): void               { $this->resetPage(); }
    public function updatingFilterStatus(): void         { $this->resetPage(); }
    public function updatingFilterPaymentStatus(): void  { $this->resetPage(); }
    public function updatingFilterDeliveryStatus(): void { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return [
            'search'               => '',
            'filterStatus'         => '',
            'filterPaymentStatus'  => '',
            'filterDeliveryStatus' => '',
        ];
    }

    // ── Limpiar filtros ───────────────────────────────────────────────────────

    public function clearFilters(): void
    {
        $this->search               = '';
        $this->filterStatus         = '';
        $this->filterPaymentStatus  = '';
        $this->filterDeliveryStatus = '';
        $this->resetPage();
    }

    // ── Emitir ────────────────────────────────────────────────────────────────

    public function openEmitirModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
        if (!$invoice) return;

        if ($invoice->status !== 'draft') {
            $this->toastError('Solo se puede emitir una factura en borrador.');
            return;
        }

        $this->emitirId    = $id;
        $this->emitirDate  = now()->toDateString();
        $this->emitirModal = true;
    }

    public function closeEmitirModal(): void
    {
        $this->emitirModal = false;
        $this->emitirId    = null;
        $this->emitirDate  = '';
        $this->resetValidation();
    }

    public function confirmEmitir(): void
    {
        $this->validate(
            ['emitirDate' => 'required|date'],
            ['emitirDate.required' => 'La fecha de factura es obligatoria.']
        );

        $invoice = $this->findInvoice($this->emitirId);
        if (!$invoice || $invoice->status !== 'draft') {
            $this->toastError('La factura ya no está en borrador.');
            $this->closeEmitirModal();
            return;
        }

        $invoiceNumber = null;

        try {
            DB::transaction(function () use ($invoice, &$invoiceNumber) {
                $settings      = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $invoice->update([
                    'invoice_number' => $invoiceNumber,
                    'invoice_date'   => $this->emitirDate,
                    'status'         => 'sent',
                ]);
            });

            $this->closeEmitirModal();
            $this->toastSuccess("Factura {$invoiceNumber} emitida correctamente.");

        } catch (\Exception $e) {
            Log::error('Error al emitir factura de vino: ' . $e->getMessage(), [
                'invoice_id' => $this->emitirId,
                'user_id'    => Auth::id(),
            ]);
            $this->toastError('Error al emitir la factura.');
        }
    }

    // ── Entregar ──────────────────────────────────────────────────────────────

    public function markDelivered(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (!$invoice) return;

        if ($invoice->status === 'cancelled') {
            $this->toastError('No se puede marcar como entregada una factura cancelada.');
            return;
        }

        if ($invoice->delivery_status === 'delivered') {
            $this->toastError('Esta factura ya está entregada.');
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                WineStockService::moveForInvoice($invoice, 'deliver');
                $invoice->update(['delivery_status' => 'delivered']);
            });

            $this->toastSuccess('Factura marcada como entregada. Stock movido a vendido.');

        } catch (\Exception $e) {
            Log::error('Error al marcar factura como entregada: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'user_id'    => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al procesar la entrega.');
        }
    }

    // ── Cancelar ──────────────────────────────────────────────────────────────

    public function cancel(int $invoiceId): void
    {
        $invoice = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (!$invoice) return;

        if ($invoice->status === 'cancelled') {
            $this->toastError('Esta factura ya está cancelada.');
            return;
        }

        if ($invoice->payment_status === 'paid') {
            $this->toastError('No se puede cancelar una factura ya cobrada.');
            return;
        }

        try {
            DB::transaction(function () use ($invoice) {
                WineStockService::moveForInvoice($invoice, 'cancel');
                $invoice->update([
                    'status'          => 'cancelled',
                    'delivery_status' => 'cancelled',
                ]);
            });

            $this->toastSuccess('Factura cancelada y stock restaurado.');

        } catch (\Exception $e) {
            Log::error('Error al cancelar factura de vino: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'user_id'    => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al cancelar la factura.');
        }
    }

    protected function markPaidSuccessMessage(): string
    {
        return 'Factura marcada como cobrada.';
    }

    // ── Rectificativa ─────────────────────────────────────────────────────────

    public function openCorrectiveModal(int $id): void
    {
        $invoice = $this->findInvoice($id);
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

        $original = $this->findInvoice($this->correctiveId, ['items.wineLot']);
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
                $settings      = InvoicingSetting::getOrCreateForUser(Auth::id());
                $invoiceNumber = $settings->generateAndIncrementInvoiceCode();

                $notes = 'Rectificativa de ' . $original->invoice_number . '.'
                    . ($this->correctiveReason ? ' Motivo: ' . $this->correctiveReason : '');

                $corrective = Invoice::withoutEvents(fn () => Invoice::create([
                    'user_id'              => Auth::id(),
                    'client_id'            => $original->client_id,
                    'client_address_id'    => $original->client_address_id,
                    'corrected_invoice_id' => $original->id,
                    'invoice_type'         => 'wine_sale',
                    'corrective'           => true,
                    'invoice_number'       => $invoiceNumber,
                    'invoice_date'         => $this->correctiveDate,
                    'order_date'           => now(),
                    'status'               => 'sent',
                    'payment_status'       => 'unpaid',
                    'delivery_status'      => 'cancelled',
                    'subtotal'             => -abs((float) $original->subtotal),
                    'discount_amount'      => -abs((float) $original->discount_amount),
                    'tax_base'             => -abs((float) $original->tax_base),
                    'tax_rate'             => $original->tax_rate,
                    'tax_amount'           => -abs((float) $original->tax_amount),
                    'total_amount'         => -abs((float) $original->total_amount),
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

                InvoiceItem::withoutObservers(function () use ($original, $corrective) {
                    foreach ($original->items as $item) {
                        $corrective->items()->create([
                            'wine_lot_id'         => $item->wine_lot_id,
                            'harvest_id'          => $item->harvest_id,
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

                WineStockService::moveForInvoice($original, 'cancel');
            });

            $this->closeCorrectiveModal();
            $this->toastSuccess("Rectificativa {$invoiceNumber} emitida. Stock de vino restaurado.");

        } catch (\Exception $e) {
            Log::error('Error al crear rectificativa de venta de vino: ' . $e->getMessage(), [
                'original_invoice_id' => $this->correctiveId,
                'user_id'             => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al generar la rectificativa.');
        }
    }

    // ── Query ─────────────────────────────────────────────────────────────────

    protected function baseQuery(): Builder
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->with(['client', 'items'])
            ->withCount('correctives');
    }

    protected function applyFilters(Builder $query): void
    {
        if ($this->search) {
            $term = '%' . mb_strtolower($this->search) . '%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                  ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term])
                  ->orWhereHas('client', function ($q2) use ($term) {
                      $q2->whereRaw('LOWER(IFNULL(first_name,\'\')) LIKE ?', [$term])
                         ->orWhereRaw('LOWER(IFNULL(company_name,\'\')) LIKE ?', [$term]);
                  });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterPaymentStatus) {
            $query->where('payment_status', $this->filterPaymentStatus);
        }

        if ($this->filterDeliveryStatus) {
            $query->where('delivery_status', $this->filterDeliveryStatus);
        }
    }

    protected function applyOrderBy(Builder $query): void
    {
        $query->orderByDesc('created_at')->orderByDesc('id');
    }

    protected function defaultOrderBy(): array { return ['created_at', 'desc']; }

    protected function perPage(): int { return 15; }

    protected function viewData(mixed $entries): array
    {
        return ['invoices' => $entries];
    }

    protected function resolveViewName(): string
    {
        return 'livewire.winery.billing.products.index';
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function findInvoice(int $id, array $with = []): ?Invoice
    {
        return Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->with($with)
            ->find($id);
    }
}
