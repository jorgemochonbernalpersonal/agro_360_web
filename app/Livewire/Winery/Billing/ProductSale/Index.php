<?php

namespace App\Livewire\Winery\Billing\ProductSale;

use App\Livewire\Concerns\WithInvoiceActions;
use App\Livewire\Winery\AbstractIndex;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicingSetting;
use App\Models\ProductLot;
use App\Models\Tax;
use App\Services\ProductStockService;
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
    public bool   $filterGift           = false;

    // Modal emitir
    public bool   $emitirModal = false;
    public ?int   $emitirId    = null;
    public string $emitirDate  = '';

    // Modal rectificativa
    public bool   $correctiveModal  = false;
    public ?int   $correctiveId     = null;
    public string $correctiveDate   = '';
    public string $correctiveReason = '';

    // Quick invoice modal
    public bool   $quickModal              = false;
    public string $quickClientId           = '';
    public string $quickClientAddressId    = '';
    public string $quickLotId              = '';
    public string $quickConceptName        = '';
    public string $quickQty                = '';
    public string $quickPrice              = '';
    public string $quickTaxId              = '';
    public string $quickPaymentType        = '';
    public array  $quickAvailableAddresses = [];
    public float  $quickAvailableQty       = 0;

    // Export modal
    public bool   $exportModal    = false;
    public string $exportDateFrom = '';
    public string $exportDateTo   = '';

    protected $queryString = [
        'search'               => ['except' => ''],
        'filterStatus'         => ['except' => ''],
        'filterPaymentStatus'  => ['except' => ''],
        'filterDeliveryStatus' => ['except' => ''],
        'filterGift'           => ['except' => false],
    ];

    public function updatingSearch(): void               { $this->resetPage(); }
    public function updatingFilterStatus(): void         { $this->resetPage(); }
    public function updatingFilterPaymentStatus(): void  { $this->resetPage(); }
    public function updatingFilterDeliveryStatus(): void { $this->resetPage(); }
    public function updatingFilterGift(): void           { $this->resetPage(); }

    protected function filterDefaults(): array
    {
        return [
            'search'               => '',
            'filterStatus'         => '',
            'filterPaymentStatus'  => '',
            'filterDeliveryStatus' => '',
            'filterGift'           => false,
        ];
    }

    // ── Limpiar filtros ───────────────────────────────────────────────────────

    public function clearFilters(): void
    {
        $this->search               = '';
        $this->filterStatus         = '';
        $this->filterPaymentStatus  = '';
        $this->filterDeliveryStatus = '';
        $this->filterGift           = false;
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
            Log::error('Error al emitir factura de productos: ' . $e->getMessage(), [
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
                ProductStockService::moveForInvoice($invoice, 'deliver');
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
                ProductStockService::moveForInvoice($invoice, 'cancel');
                $invoice->update([
                    'status'          => 'cancelled',
                    'delivery_status' => 'cancelled',
                ]);
            });

            $this->toastSuccess('Factura cancelada y stock restaurado.');

        } catch (\Exception $e) {
            Log::error('Error al cancelar factura de productos: ' . $e->getMessage(), [
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

                InvoiceItem::withoutEvents(function () use ($original, $corrective) {
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

                ProductStockService::moveForInvoice($original, 'cancel');
            });

            $this->closeCorrectiveModal();
            $this->toastSuccess("Rectificativa {$invoiceNumber} emitida. Stock restaurado.");

        } catch (\Exception $e) {
            Log::error('Error al crear rectificativa de venta de productos: ' . $e->getMessage(), [
                'original_invoice_id' => $this->correctiveId,
                'user_id'             => Auth::id(),
            ]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al generar la rectificativa.');
        }
    }

    // ── Duplicar ──────────────────────────────────────────────────────────────

    public function duplicate(int $invoiceId): void
    {
        $original = $this->findInvoice($invoiceId, ['items.wineLot']);
        if (!$original) return;

        try {
            DB::transaction(function () use ($original) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $noteCode = $settings->generateAndIncrementDeliveryNoteCode();

                $newInvoice = Invoice::create([
                    'user_id'                  => Auth::id(),
                    'client_id'                => $original->client_id,
                    'client_address_id'        => $original->client_address_id,
                    'invoice_type'             => 'wine_sale',
                    'delivery_note_code'       => $noteCode,
                    'delivery_note_date'       => now()->toDateString(),
                    'order_date'               => now()->toDateString(),
                    'invoice_date'             => null,
                    'delivery_status'          => 'pending',
                    'status'                   => 'draft',
                    'payment_status'           => 'unpaid',
                    'payment_type'             => $original->payment_type,
                    'billing_first_name'           => $original->billing_first_name,
                    'billing_last_name'            => $original->billing_last_name,
                    'billing_company_name'         => $original->billing_company_name,
                    'billing_company_document'     => $original->billing_company_document,
                    'billing_email'                => $original->billing_email,
                    'billing_phone'                => $original->billing_phone,
                    'billing_address'              => $original->billing_address,
                    'billing_postal_code'          => $original->billing_postal_code,
                    'billing_city'                 => $original->billing_city,
                    'billing_state'                => $original->billing_state,
                    'billing_country'              => $original->billing_country,
                    'gift'                         => $original->gift,
                    'tax_rate'                     => $original->tax_rate,
                    'subtotal'                 => $original->subtotal,
                    'discount_amount'          => $original->discount_amount,
                    'tax_base'                 => $original->tax_base,
                    'tax_amount'               => $original->tax_amount,
                    'total_amount'             => $original->total_amount,
                    'observations'             => $original->observations,
                    'observations_invoice'     => $original->observations_invoice,
                ]);

                foreach ($original->items as $item) {
                    $lot = $item->wine_lot_id
                        ? ProductLot::where('user_id', Auth::id())->lockForUpdate()->find($item->wine_lot_id)
                        : null;

                    $createdItem = InvoiceItem::create([
                        'invoice_id'          => $newInvoice->id,
                        'wine_lot_id'         => $lot?->id,
                        'concept_type'        => $item->concept_type,
                        'name'                => $item->name,
                        'description'         => $item->description,
                        'sku'                 => $item->sku,
                        'quantity'            => $item->quantity,
                        'unit_price'          => $item->unit_price,
                        'discount_percentage' => $item->discount_percentage,
                        'discount_amount'     => $item->discount_amount,
                        'tax_id'              => $item->tax_id,
                        'tax_name'            => $item->tax_name,
                        'tax_rate'            => $item->tax_rate,
                        'subtotal'            => $item->subtotal,
                        'tax_base'            => $item->tax_base,
                        'tax_amount'          => $item->tax_amount,
                        'total'               => $item->total,
                    ]);

                    if ($lot) {
                        ProductStockService::moveOnCreate($newInvoice, $createdItem, $lot, (float) $item->quantity);
                    }
                }
            });

            $this->toastSuccess('Albarán duplicado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al duplicar factura: ' . $e->getMessage(), ['invoice_id' => $invoiceId]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al duplicar el albarán.');
        }
    }

    // ── Factura rápida ────────────────────────────────────────────────────────

    public function openQuickModal(): void
    {
        $this->resetQuickModal();
        $user = Auth::user();
        $taxes = $user->taxes()->orderByPivot('order')->get();
        if ($taxes->isEmpty()) {
            $taxes = Tax::active()->orderBy('rate')->get();
        }
        $default = $user->defaultTax()->first() ?? $taxes->first();
        $this->quickTaxId = (string) ($default?->id ?? '');
        $this->quickModal = true;
    }

    public function closeQuickModal(): void
    {
        $this->quickModal = false;
        $this->resetQuickModal();
        $this->resetValidation();
    }

    private function resetQuickModal(): void
    {
        $this->quickClientId           = '';
        $this->quickClientAddressId    = '';
        $this->quickLotId              = '';
        $this->quickConceptName        = '';
        $this->quickQty                = '';
        $this->quickPrice              = '';
        $this->quickTaxId              = '';
        $this->quickPaymentType        = '';
        $this->quickAvailableAddresses = [];
        $this->quickAvailableQty       = 0;
    }

    public function updatedQuickClientId(string $value): void
    {
        if ($value) {
            $client = Client::with('addresses')->find($value);
            $this->quickAvailableAddresses = $client?->addresses ?? collect();
            $primary = $client?->addresses->firstWhere('is_default', true) ?? $client?->addresses->first();
            $this->quickClientAddressId = $primary ? (string) $primary->id : '';
        } else {
            $this->quickAvailableAddresses = [];
            $this->quickClientAddressId = '';
        }
    }

    public function updatedQuickLotId(string $value): void
    {
        if ($value) {
            $lot = ProductLot::where('user_id', Auth::id())->find($value);
            if ($lot) {
                $this->quickConceptName  = $lot->name . ($lot->vintage ? " ({$lot->vintage})" : '');
                $this->quickPrice        = (string) ($lot->price_per_unit ?? 0);
                $this->quickAvailableQty = (float) $lot->available_quantity;
            }
        }
    }

    public function confirmQuick(): void
    {
        $this->validate(
            [
                'quickClientId'        => 'required|exists:clients,id',
                'quickClientAddressId' => 'required|exists:client_addresses,id',
                'quickLotId'           => 'required|exists:wine_lots,id',
                'quickConceptName'     => 'required|string|max:255',
                'quickQty'             => 'required|numeric|min:0.001',
                'quickPrice'           => 'required|numeric|min:0',
                'quickTaxId'           => 'nullable|exists:taxes,id',
                'quickPaymentType'     => 'nullable|in:cash,transfer,check,other',
            ],
            [
                'quickClientId.required'        => 'Selecciona un cliente.',
                'quickClientAddressId.required' => 'Selecciona una dirección.',
                'quickLotId.required'           => 'Selecciona un lote de producto.',
                'quickConceptName.required'     => 'El concepto es obligatorio.',
                'quickQty.required'             => 'La cantidad es obligatoria.',
                'quickPrice.required'           => 'El precio es obligatorio.',
            ]
        );

        $client   = Client::where('user_id', Auth::id())->findOrFail($this->quickClientId);
        $taxes    = Auth::user()->taxes()->get()->keyBy('id');
        if ($taxes->isEmpty()) {
            $taxes = Tax::active()->get()->keyBy('id');
        }

        $qty      = (float) $this->quickQty;
        $price    = (float) $this->quickPrice;
        $tax      = $this->quickTaxId ? ($taxes[$this->quickTaxId] ?? null) : null;
        $taxRate  = $tax ? (float) $tax->rate : 0;
        $subtotal = round($qty * $price, 3);
        $taxAmt   = round($subtotal * ($taxRate / 100), 3);
        $total    = $subtotal + $taxAmt;

        try {
            DB::transaction(function () use ($client, $tax, $qty, $price, $taxRate, $subtotal, $taxAmt, $total) {
                $settings = InvoicingSetting::getOrCreateForUser(Auth::id());
                $noteCode = $settings->generateAndIncrementDeliveryNoteCode();

                $invoice = Invoice::create([
                    'user_id'              => Auth::id(),
                    'client_id'            => $client->id,
                    'client_address_id'    => $this->quickClientAddressId ?: null,
                    'invoice_type'         => 'wine_sale',
                    'delivery_note_code'   => $noteCode,
                    'delivery_note_date'   => now()->toDateString(),
                    'invoice_date'         => now()->toDateString(),
                    'delivery_status'      => 'pending',
                    'status'               => 'draft',
                    'payment_status'       => 'unpaid',
                    'payment_type'         => $this->quickPaymentType ?: null,
                    'billing_first_name'   => $client->first_name,
                    'billing_last_name'    => $client->last_name,
                    'billing_company_name' => $client->company_name,
                    'billing_email'        => $client->email,
                    'billing_phone'        => $client->phone,
                    'subtotal'             => $subtotal,
                    'discount_amount'      => 0,
                    'tax_base'             => $subtotal,
                    'tax_amount'           => $taxAmt,
                    'total_amount'         => $total,
                ]);

                $lot         = ProductLot::where('user_id', Auth::id())->lockForUpdate()->findOrFail($this->quickLotId);
                $createdItem = InvoiceItem::create([
                    'invoice_id'          => $invoice->id,
                    'wine_lot_id'         => $lot->id,
                    'concept_type'        => 'wine',
                    'name'                => $this->quickConceptName,
                    'quantity'            => $qty,
                    'unit_price'          => $price,
                    'discount_percentage' => 0,
                    'discount_amount'     => 0,
                    'tax_id'              => $tax?->id,
                    'tax_name'            => $tax?->name,
                    'tax_rate'            => $taxRate,
                    'subtotal'            => $subtotal,
                    'tax_base'            => $subtotal,
                    'tax_amount'          => $taxAmt,
                    'total'               => $total,
                ]);

                ProductStockService::moveOnCreate($invoice, $createdItem, $lot, $qty);
            });

            $this->closeQuickModal();
            $this->toastSuccess('Albarán rápido creado correctamente.');

        } catch (\Exception $e) {
            Log::error('Error al crear albarán rápido: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            $this->toastError($e instanceof \RuntimeException ? $e->getMessage() : 'Error al crear el albarán.');
        }
    }

    // ── Exportar ──────────────────────────────────────────────────────────────

    public function openExportModal(): void
    {
        $this->exportDateFrom = now()->startOfMonth()->toDateString();
        $this->exportDateTo   = now()->toDateString();
        $this->exportModal    = true;
    }

    public function closeExportModal(): void
    {
        $this->exportModal = false;
        $this->resetValidation();
    }

    public function export()
    {
        $this->validate(
            [
                'exportDateFrom' => 'required|date',
                'exportDateTo'   => 'required|date|after_or_equal:exportDateFrom',
            ],
            [
                'exportDateTo.after_or_equal' => 'La fecha final no puede ser anterior a la inicial.',
            ]
        );

        $this->closeExportModal();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProductSaleInvoiceExport(Auth::id(), $this->exportDateFrom, $this->exportDateTo),
            'facturas_venta_' . $this->exportDateFrom . '_' . $this->exportDateTo . '.xlsx'
        );
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

        if ($this->filterGift) {
            $query->where('gift', true);
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
        $giftCount = Invoice::where('user_id', $this->wineryId())
            ->where('invoice_type', 'wine_sale')
            ->where('gift', true)
            ->where('status', '!=', 'cancelled')
            ->when($this->search, function ($q) {
                $term = '%' . mb_strtolower($this->search) . '%';
                $q->where(function ($q2) use ($term) {
                    $q2->whereRaw('LOWER(IFNULL(invoice_number,\'\')) LIKE ?', [$term])
                       ->orWhereRaw('LOWER(IFNULL(delivery_note_code,\'\')) LIKE ?', [$term]);
                });
            })
            ->when($this->filterStatus, fn ($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterPaymentStatus, fn ($q) => $q->where('payment_status', $this->filterPaymentStatus))
            ->when($this->filterDeliveryStatus, fn ($q) => $q->where('delivery_status', $this->filterDeliveryStatus))
            ->count();

        return ['invoices' => $entries, 'giftCount' => $giftCount];
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
