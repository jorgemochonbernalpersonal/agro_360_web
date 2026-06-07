<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreWineSaleInvoiceRequest;
use App\Http\Requests\Api\Winery\UpdateWineSaleInvoiceRequest;
use App\Http\Requests\Api\Winery\WineryApiRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseApiController
{
    // ─── GET /winery/invoices ─────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
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

        return $this->paginated($invoices, InvoiceResource::collection($invoices));
    }

    // ─── GET /winery/invoices/{id} ────────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('user_id', $request->user()->id)
            ->with('client')
            ->findOrFail($id);

        return $this->success(new InvoiceResource($invoice));
    }

    // ─── POST /winery/invoices ────────────────────────────────────────────────

    public function store(StoreWineSaleInvoiceRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        // Auto-assign invoice number if not provided
        if (empty($validated['invoice_number'])) {
            $settings = \App\Models\InvoicingSetting::getOrCreateForUser($user->id);
            $validated['invoice_number'] = $settings->generateAndIncrementInvoiceCode();
        }

        // Compute tax_amount from subtotal + tax_rate when not provided
        if (isset($validated['subtotal']) && isset($validated['tax_rate']) && ! isset($validated['total_amount'])) {
            $taxAmount = round($validated['subtotal'] * $validated['tax_rate'] / 100, 3);
            $validated['tax_amount'] = $taxAmount;
            $validated['total_amount'] = round($validated['subtotal'] + $taxAmount, 3);
        }

        $invoice = Invoice::create(array_merge($validated, [
            'user_id' => $user->id,
            'status' => $validated['status'] ?? 'draft',
            'payment_status' => 'unpaid',
            'invoice_type' => $validated['invoice_type'] ?? 'standard',
        ]));

        $invoice->load('client');

        return $this->created(new InvoiceResource($invoice));
    }

    // ─── PUT /winery/invoices/{id} ────────────────────────────────────────────

    public function update(UpdateWineSaleInvoiceRequest $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('user_id', $request->user()->id)->findOrFail($id);

        $invoice->update($request->validated());
        $invoice->load('client');

        return $this->success(new InvoiceResource($invoice));
    }

    // ─── DELETE /winery/invoices/{id} ─────────────────────────────────────────

    public function destroy(WineryApiRequest $request, int $id): JsonResponse
    {
        $invoice = Invoice::where('user_id', $request->user()->id)->findOrFail($id);
        $invoice->delete();

        return $this->deleted(__('Factura eliminada correctamente.'));
    }
}
