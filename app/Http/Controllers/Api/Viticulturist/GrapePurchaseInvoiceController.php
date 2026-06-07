<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\GrapePurchaseInvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GrapePurchaseInvoiceController extends BaseApiController
{
    // ─── GET /viticulturist/grape-purchase-invoices ──────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'payment_status' => 'nullable|string|in:unpaid,paid,overdue',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Invoice::where('viticulturist_id', $user->id)
            ->with(['viticulturist', 'items'])
            ->orderByDesc('invoice_date');

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, GrapePurchaseInvoiceResource::collection($items->items()));
    }
}
