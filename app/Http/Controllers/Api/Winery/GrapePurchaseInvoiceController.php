<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\GrapePurchaseInvoiceResource;
use App\Models\Harvest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrapePurchaseInvoiceController extends Controller
{
    private const INVOICE_TYPE = 'grape_purchase';

    // ─── GET /winery/grape-invoices ───────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'viticulturist_id' => 'nullable|integer',
            'status'           => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_status'   => 'nullable|string|in:unpaid,partial,paid,overdue',
            'per_page'         => 'nullable|integer|min:1|max:100',
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

        $perPage  = $this->resolvePerPage($request, 20, 100);
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
                'total'              => $invoices->total(),
                'per_page'           => $invoices->perPage(),
                'current_page'       => $invoices->currentPage(),
                'last_page'          => $invoices->lastPage(),
                'draft_count'        => (int) $totals->draft_count,
                'unpaid_count'       => (int) $totals->unpaid_count,
                'total_paid_amount'  => (float) $totals->total_paid_amount,
            ],
        ]);
    }

    // ─── GET /winery/grape-invoices/{id} ─────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->with(['viticulturist', 'items'])
            ->findOrFail($id);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── POST /winery/grape-invoices ──────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'viticulturist_id'    => 'required|integer|exists:users,id',
            'invoice_number'      => 'nullable|string|max:50',
            'invoice_date'        => 'required|date',
            'payment_date'        => 'nullable|date',
            'payment_type'        => 'nullable|string|in:transfer,cash,card,check,other',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'payment_details'     => 'nullable|string|max:500',
            'observations'        => 'nullable|string|max:2000',
            // Líneas de la liquidación
            'items'                       => 'required|array|min:1',
            'items.*.harvest_id'          => 'nullable|integer|exists:harvests,id',
            'items.*.name'                => 'required|string|max:255',
            'items.*.description'         => 'nullable|string|max:500',
            'items.*.quantity'            => 'required|numeric|min:0.001',
            'items.*.unit'                => 'required|string|max:20',
            'items.*.unit_price'          => 'required|numeric|min:0',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate'            => 'nullable|numeric|min:0|max:100',
        ]);

        // Verificar que el viticultor pertenece a esta bodega
        $isLinked = WineryViticulturist::where('winery_id', $user->id)
            ->where('viticulturist_id', $validated['viticulturist_id'])
            ->exists();
        abort_unless($isLinked, 422, 'El viticultor no está vinculado a esta bodega.');

        // Verificar que las recepciones pertenecen a esta bodega
        $harvestIds = collect($validated['items'])->pluck('harvest_id')->filter()->unique();
        if ($harvestIds->isNotEmpty()) {
            $invalid = Harvest::whereIn('id', $harvestIds)
                ->where('winery_id', '!=', $user->id)
                ->exists();
            abort_if($invalid, 422, 'Alguna recepción de uva no pertenece a esta bodega.');
        }

        $itemsData = $validated['items'];
        unset($validated['items']);

        $invoice = DB::transaction(function () use ($validated, $itemsData, $user) {
            $invoice = Invoice::create([
                ...$validated,
                'user_id'        => $user->id,
                'invoice_type'   => self::INVOICE_TYPE,
                'status'         => 'draft',
                'payment_status' => 'unpaid',
            ]);

            foreach ($itemsData as $item) {
                InvoiceItem::create([
                    'invoice_id'          => $invoice->id,
                    'harvest_id'          => $item['harvest_id'] ?? null,
                    'name'                => $item['name'],
                    'description'         => $item['description'] ?? null,
                    'concept_type'        => 'harvest',
                    'quantity'            => $item['quantity'],
                    'unit'                => $item['unit'],
                    'unit_price'          => $item['unit_price'],
                    'discount_percentage' => $item['discount_percentage'] ?? 0,
                    'tax_rate'            => $item['tax_rate'] ?? 0,
                ]);
            }

            $this->recalculateInvoiceTotals($invoice);

            return $invoice;
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data'    => new GrapePurchaseInvoiceResource($invoice),
            'message' => 'Liquidación creada correctamente.',
        ], 201);
    }

    // ─── PUT /winery/grape-invoices/{id} ─────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede editar una liquidación cancelada.');

        $validated = $request->validate([
            'invoice_number'      => 'sometimes|nullable|string|max:50',
            'invoice_date'        => 'sometimes|date',
            'payment_date'        => 'sometimes|nullable|date',
            'payment_type'        => 'sometimes|nullable|string|in:transfer,cash,card,check,other',
            'bank_name'           => 'sometimes|nullable|string|max:100',
            'bank_account_number' => 'sometimes|nullable|string|max:50',
            'payment_details'     => 'sometimes|nullable|string|max:500',
            'observations'        => 'sometimes|nullable|string|max:2000',
        ]);

        $invoice->update($validated);
        $invoice->load(['viticulturist', 'items']);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── DELETE /winery/grape-invoices/{id} ──────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->payment_status === 'paid', 422, 'No se puede eliminar una liquidación ya pagada.');

        DB::transaction(function () use ($invoice) {
            $invoice->items()->delete();
            $invoice->delete();
        });

        return response()->json(['message' => 'Liquidación eliminada correctamente.']);
    }

    // ─── POST /winery/grape-invoices/{id}/items ───────────────────────────────

    public function addItem(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $validated = $request->validate([
            'harvest_id'          => 'nullable|integer|exists:harvests,id',
            'name'                => 'required|string|max:255',
            'description'         => 'nullable|string|max:500',
            'quantity'            => 'required|numeric|min:0.001',
            'unit'                => 'required|string|max:20',
            'unit_price'          => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_rate'            => 'nullable|numeric|min:0|max:100',
        ]);

        if (isset($validated['harvest_id'])) {
            $invalid = Harvest::where('id', $validated['harvest_id'])
                ->where('winery_id', '!=', $user->id)
                ->exists();
            abort_if($invalid, 422, 'La recepción de uva no pertenece a esta bodega.');
        }

        DB::transaction(function () use ($invoice, $validated) {
            InvoiceItem::create([
                'invoice_id'          => $invoice->id,
                'harvest_id'          => $validated['harvest_id'] ?? null,
                'name'                => $validated['name'],
                'description'         => $validated['description'] ?? null,
                'concept_type'        => 'harvest',
                'quantity'            => $validated['quantity'],
                'unit'                => $validated['unit'],
                'unit_price'          => $validated['unit_price'],
                'discount_percentage' => $validated['discount_percentage'] ?? 0,
                'tax_rate'            => $validated['tax_rate'] ?? 0,
            ]);

            $this->recalculateInvoiceTotals($invoice);
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data'    => new GrapePurchaseInvoiceResource($invoice),
            'message' => 'Línea añadida correctamente.',
        ]);
    }

    // ─── PUT /winery/grape-invoices/{id}/items/{itemId} ──────────────────────

    public function updateItem(Request $request, int $id, int $itemId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);

        $validated = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'description'         => 'sometimes|nullable|string|max:500',
            'quantity'            => 'sometimes|numeric|min:0.001',
            'unit'                => 'sometimes|string|max:20',
            'unit_price'          => 'sometimes|numeric|min:0',
            'discount_percentage' => 'sometimes|nullable|numeric|min:0|max:100',
            'tax_rate'            => 'sometimes|nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($item, $invoice, $validated) {
            $item->update($validated);
            $this->recalculateInvoiceTotals($invoice);
        });

        $invoice->load(['viticulturist', 'items']);

        return response()->json(['data' => new GrapePurchaseInvoiceResource($invoice)]);
    }

    // ─── DELETE /winery/grape-invoices/{id}/items/{itemId} ───────────────────

    public function removeItem(Request $request, int $id, int $itemId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede modificar una liquidación cancelada.');

        $item = InvoiceItem::where('invoice_id', $invoice->id)->findOrFail($itemId);

        DB::transaction(function () use ($item, $invoice) {
            $item->delete();
            $this->recalculateInvoiceTotals($invoice);
        });

        return response()->json(['message' => 'Línea eliminada correctamente.']);
    }

    // ─── POST /winery/grape-invoices/{id}/confirm ─────────────────────────────

    public function confirm(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_unless($invoice->status === 'draft', 422, 'Solo se pueden confirmar liquidaciones en borrador.');

        $invoice->update(['status' => 'sent']);
        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data'    => new GrapePurchaseInvoiceResource($invoice),
            'message' => 'Liquidación confirmada correctamente.',
        ]);
    }

    // ─── POST /winery/grape-invoices/{id}/mark-paid ───────────────────────────

    public function markPaid(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)
            ->where('invoice_type', self::INVOICE_TYPE)
            ->findOrFail($id);

        abort_if($invoice->status === 'cancelled', 422, 'No se puede marcar como pagada una liquidación cancelada.');

        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_type' => 'nullable|string|in:transfer,cash,card,check,other',
        ]);

        $invoice->update([
            'payment_status' => 'paid',
            'payment_date'   => $validated['payment_date'] ?? now()->toDateString(),
            'payment_type'   => $validated['payment_type'] ?? $invoice->payment_type,
        ]);

        $invoice->load(['viticulturist', 'items']);

        return response()->json([
            'data'    => new GrapePurchaseInvoiceResource($invoice),
            'message' => 'Liquidación marcada como pagada.',
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function recalculateInvoiceTotals(Invoice $invoice): void
    {
        $items = InvoiceItem::where('invoice_id', $invoice->id)->get();

        $subtotal  = $items->sum(fn ($i) => (float) $i->subtotal);
        $taxAmount = $items->sum(fn ($i) => (float) $i->tax_amount);
        $taxBase   = $items->sum(fn ($i) => (float) ($i->tax_base ?? $i->subtotal));

        $invoice->update([
            'subtotal'     => round($subtotal, 3),
            'tax_base'     => round($taxBase, 3),
            'tax_amount'   => round($taxAmount, 3),
            'total_amount' => round($subtotal - $taxAmount, 3),
        ]);
    }
}
