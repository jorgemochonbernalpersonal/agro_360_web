<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Viticulturist\IndexHarvestSaleInvoiceRequest;
use App\Http\Resources\Api\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;

class HarvestSaleInvoiceController extends Controller
{
    // ─── GET /viticulturist/harvest-sale-invoices ────────────────────────────

    public function index(IndexHarvestSaleInvoiceRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = Invoice::forUser($user->id)
            ->where('invoice_type', 'harvest_sale')
            ->with('client')
            ->orderByDesc('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%")
                    ->orWhere('billing_company_name', 'like', "%{$term}%")
                    ->orWhere('billing_first_name', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => InvoiceResource::collection($items->items()),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }
}
