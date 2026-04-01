<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    // ─── GET /winery/invoices ─────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $query = Invoice::where('user_id', $user->id)
            ->with('client')
            ->latest('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        $invoices = $query->paginate($perPage);

        return response()->json([
            'data' => InvoiceResource::collection($invoices),
            'meta' => [
                'total'        => $invoices->total(),
                'current_page' => $invoices->currentPage(),
                'last_page'    => $invoices->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/invoices/{id} ────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        $invoice = Invoice::where('user_id', $user->id)
            ->with('client')
            ->findOrFail($id);

        return response()->json(['data' => new InvoiceResource($invoice)]);
    }

    // ─── POST /winery/invoices ────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'client_id'      => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:50',
            'invoice_date'   => 'required|date',
            'invoice_type'   => 'nullable|string|in:standard,corrective,receipt',
            'status'         => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_type'   => 'nullable|string|in:transfer,cash,card,check,other',
            'subtotal'       => 'nullable|numeric|min:0',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'total_amount'   => 'nullable|numeric|min:0',
            'gift'           => 'nullable|boolean',
            'observations'   => 'nullable|string|max:2000',
        ]);

        // Compute tax_amount from subtotal + tax_rate when not provided
        if (isset($validated['subtotal']) && isset($validated['tax_rate']) && !isset($validated['total_amount'])) {
            $taxAmount = round($validated['subtotal'] * $validated['tax_rate'] / 100, 3);
            $validated['tax_amount']   = $taxAmount;
            $validated['total_amount'] = round($validated['subtotal'] + $taxAmount, 3);
        }

        $invoice = Invoice::create(array_merge($validated, [
            'user_id'        => $user->id,
            'status'         => $validated['status'] ?? 'draft',
            'payment_status' => 'unpaid',
            'invoice_type'   => $validated['invoice_type'] ?? 'standard',
        ]));

        $invoice->load('client');

        return response()->json(['data' => new InvoiceResource($invoice)], 201);
    }

    // ─── PUT /winery/invoices/{id} ────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'client_id'      => 'nullable|integer',
            'invoice_number' => 'nullable|string|max:50',
            'invoice_date'   => 'nullable|date',
            'invoice_type'   => 'nullable|string|in:standard,corrective,receipt',
            'status'         => 'nullable|string|in:draft,sent,paid,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid,overdue',
            'payment_type'   => 'nullable|string|in:transfer,cash,card,check,other',
            'subtotal'       => 'nullable|numeric|min:0',
            'tax_rate'       => 'nullable|numeric|min:0|max:100',
            'total_amount'   => 'nullable|numeric|min:0',
            'gift'           => 'nullable|boolean',
            'observations'   => 'nullable|string|max:2000',
        ]);

        $invoice->update($validated);
        $invoice->load('client');

        return response()->json(['data' => new InvoiceResource($invoice)]);
    }

    // ─── DELETE /winery/invoices/{id} ─────────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user    = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $invoice = Invoice::where('user_id', $user->id)->findOrFail($id);
        $invoice->delete();

        return response()->json(['message' => 'Factura eliminada correctamente.']);
    }
}
