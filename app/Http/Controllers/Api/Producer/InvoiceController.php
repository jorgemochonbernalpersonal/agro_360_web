<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoiceController extends BaseApiController
{
    // ─── GET /producer/invoices ───────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = Invoice::with('client')
            ->where('user_id', $userId)
            ->orderByDesc('invoice_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginated = $query->paginate(50);

        $items = $paginated->getCollection()->map(fn ($inv) => [
            'id' => $inv->id,
            'invoice_number' => $inv->invoice_number,
            'invoice_date' => $inv->invoice_date,
            'status' => $inv->status,
            'total_amount' => $inv->total_amount !== null ? (float) $inv->total_amount : null,
            'client_name' => $inv->client?->name ?? $inv->company_name ?? null,
            'notes' => $inv->observations,
        ]);

        return response()->json([
            'data' => $items,
            'has_more' => $paginated->hasMorePages(),
            'total' => $paginated->total(),
        ]);
    }

    // ─── GET /producer/invoices/summary ──────────────────────────────────────

    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $base = Invoice::where('user_id', $userId);
        $total = (float) ($base->clone()->sum('total_amount') ?? 0);
        $pending = (float) (Invoice::where('user_id', $userId)->whereIn('status', ['draft', 'sent'])->sum('total_amount') ?? 0);
        $paid = (float) (Invoice::where('user_id', $userId)->where('status', 'paid')->sum('total_amount') ?? 0);

        return response()->json([
            'total_invoices' => $base->count(),
            'total_amount' => $total,
            'pending_amount' => $pending,
            'paid_amount' => $paid,
        ]);
    }
}
