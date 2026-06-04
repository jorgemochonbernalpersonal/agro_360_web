<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Winery\MarkGrapePurchaseInvoicePaidRequest;
use App\Http\Requests\Api\Winery\StoreGrapePurchaseInvoiceItemRequest;
use App\Http\Requests\Api\Winery\StoreGrapePurchaseInvoiceRequest;
use App\Http\Requests\Api\Winery\UpdateGrapePurchaseInvoiceItemRequest;
use App\Http\Requests\Api\Winery\UpdateGrapePurchaseInvoiceRequest;
use App\Http\Requests\Api\Winery\WineryApiRequest;
use App\Http\Resources\Api\GrapePurchaseInvoiceResource;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GrapePurchaseInvoiceController extends Controller
{
    private const INVOICE_TYPE = 'grape_purchase';

    // ─── GET /winery/grape-invoices ───────────────────────────────────────────

    public function index(WineryApiRequest $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'viticulturist_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid,overdue',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->with('viticulturist')
            ->latest('invoice_date');

        if ($request->filled('viticulturist_id')) {
            $query->where('viticulturist_id', $request->integer('viticulturist_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $invoices = $query->paginate($perPage);

        // Aggregates
        $totals = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as draft_count,
                SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END) as unpaid_count,
                SUM(CASE WHEN status NOT IN (?, ?) THEN total_amount ELSE 0 END) as total_paid_amount
            ', ['draft', 'unpaid', 'draft', 'cancelled'])
            ->first();

        return response()->json([
            'data' => GrapePurchaseInvoiceResource::collection($invoices),
            'meta' => [
                'total' => $invoices->total(),
                'per_page' => $invoices->perPage(),
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'draft_count' => (int) $totals->draft_count,
                'unpaid_count' => (int) $totals->unpaid_count,
                'total_paid_amount' => (float) $totals->total_paid_amount,
            ],
        ]);
    }

    // ─── GET /winery/grape-invoices/{id} ─────────────────────────────────────

    public function show(WineryApiRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id, ['viticulturist', 'items']);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── POST /winery/grape-invoices ──────────────────────────────────────────

    public function store(StoreGrapePurchaseInvoiceRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $itemsData = $validated['items'];
        unset($validated['items']);

        // Auto-assign invoice number if not provided
        if (empty($validated['invoice_number'])) {
            $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
            $validated['invoice_number'] = $settings->generateAndIncrementInvoiceCode();
        }

        $invoice = DB::transaction(function () use ($validated, $itemsData, $user) {
            $invoice = Invoice::create([
                ...$validated,
                'user_id' => $user->id,
                'invoice_type' => self::INVOICE_TYPE,
                'status' => 'draft',
                'payment_status' => 'unpaid',
            ]);

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'harvest_id' => $item['harvest_id'] ?? null,
                    'name' => $item['name'],
                    'description' => $item['description'] ?? null,
                    'concept_type' => 'harvest',
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                ]);
            }

            $this->recalculateInvoiceTotals($invoice);

            return $invoice;
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data' => new GrapePurchaseInvoiceResource($invoice),
            'message' => __('Liquidación creada correctamente.'),
        ], 201);
    }

    // ─── PUT /winery/grape-invoices/{id} ─────────────────────────────────────

    public function update(UpdateGrapePurchaseInvoiceRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede editar una liquidación cancelada.');

        $invoice->update($request->validated());
        $invoice->load(['viticulturist', 'items']);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── DELETE /winery/grape-invoices/{id} ──────────────────────────────────

    public function destroy(WineryApiRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->payment_status === 'paid', 422, 'No se puede eliminar una liquidación ya pagada.');

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return response()->json(['message' => __('Liquidación eliminada correctamente.')]);
    }

    // ─── POST /winery/grape-invoices/{id}/items ───────────────────────────────

    public function addItem(StoreGrapePurchaseInvoiceItemRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $validated = $request->validated();

        DB::transaction(function () use ($invoice, $validated) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'harvest_id' => $validated['harvest_id'] ?? null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'concept_type' => 'harvest',
                'quantity' => $validated['quantity'],
                'unit' => $validated['unit'],
                'unit_price' => $validated['unit_price'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_rate' => $validated['tax_rate'] ?? 0,
            ]);

            $this->recalculateInvoiceTotals($invoice);
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data' => new GrapePurchaseInvoiceResource($invoice),
            'message' => __('Línea añadida correctamente.'),
        ]);
    }

    // ─── PUT /winery/grape-invoices/{id}/items/{itemId} ──────────────────────

    public function updateItem(UpdateGrapePurchaseInvoiceItemRequest $request, int $id, int $itemId): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);

        DB::transaction(function () use ($item, $invoice, $request) {
            $item->update($request->validated());
            $this->recalculateInvoiceTotals($invoice);
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── DELETE /winery/grape-invoices/{id}/items/{itemId} ───────────────────

    public function removeItem(WineryApiRequest $request, int $id, int $itemId): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);

        DB::transaction(function () use ($item, $invoice) {
            $item->delete();
            $this->recalculateInvoiceTotals($invoice);
        });

        return response()->json(['message' => __('Línea eliminada correctamente.')]);
    }

    // ─── POST /winery/grape-invoices/{id}/confirm ─────────────────────────────

    public function confirm(WineryApiRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_unless($invoice->status === 'draft', 422, 'Solo se pueden confirmar liquidaciones en borrador.');

        $invoice->update(['status' => 'sent']);
        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data' => new GrapePurchaseInvoiceResource($invoice),
            'message' => __('Liquidación confirmada correctamente.'),
        ]);
    }

    // ─── POST /winery/grape-invoices/{id}/mark-paid ───────────────────────────

    public function markPaid(MarkGrapePurchaseInvoicePaidRequest $request, int $id): JsonResponse
    {
        $invoice = $this->findOwnedInvoice($request->user()->id, $id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede marcar como pagada una liquidación cancelada.');

        $validated = $request->validated();

        $invoice->update([
            'payment_status' => 'paid',
            'payment_date' => $validated['payment_date'] ?? now()->toDateString(),
            'payment_type' => $validated['payment_type'] ?? $invoice->payment_type,
        ]);

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data' => new GrapePurchaseInvoiceResource($invoice),
            'message' => __('Liquidación marcada como pagada.'),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Carga una liquidación de la bodega autenticada o lanza 404.
     *
     * El scoping por user_id (en lugar de authorize() sobre el modelo encontrado)
     * es intencionado: devuelve 404 ante facturas de otra bodega para no filtrar
     * su existencia.
     */
    private function findOwnedInvoice(int $userId, int $id, array $with = []): Invoice
    {
        return Invoice::where('user_id', $userId)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->with($with)
            ->findOrFail($id);
    }

    private function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();

        $subtotal = $items->sum(fn ($i) => (float) $i->subtotal);
        $taxAmount = $items->sum(fn ($i) => (float) $i->tax_amount);
        $taxBase = $items->sum(fn ($i) => (float) ($i->tax_base ?? $i->subtotal));

        $invoice->update([
            'subtotal' => round($subtotal, 3),
            'tax_base' => round($taxBase, 3),
            'tax_amount' => round($taxAmount, 3),
            'total_amount' => round($subtotal + $taxAmount, 3),
        ]);
    }
}
