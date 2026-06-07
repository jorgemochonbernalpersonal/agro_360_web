<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Invoice;
use App\Models\VerifactuRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerifactuController extends BaseApiController
{
    // GET /winery/verifactu
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = VerifactuRecord::forUser($user->id)
            ->with('invoice')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->byStatus($request->input('status'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        // Count pending invoices not yet in VeriFactu
        $pendingCount = Invoice::where('user_id', $user->id)
            ->whereDoesntHave('verifactuRecord')
            ->whereIn('payment_status', ['paid'])
            ->count();

        return $this->paginated($items, $items->map(fn ($r) => $this->format($r)), [
            'pending_invoices' => $pendingCount,
            'statuses' => VerifactuRecord::STATUSES,
        ]);
    }

    // GET /winery/verifactu/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $record = VerifactuRecord::forUser($user->id)->with('invoice')->findOrFail($id);

        return $this->success($this->format($record));
    }

    // POST /winery/verifactu/submit — queue an invoice for VeriFactu submission
    public function submit(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
        ]);

        // Verify invoice ownership
        $invoice = Invoice::where('user_id', $user->id)->findOrFail($validated['invoice_id']);

        // Create or update VeriFactu record
        $record = VerifactuRecord::updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'user_id' => $user->id,
                'submission_status' => 'queued',
            ]
        );

        return $this->created($this->format($record->fresh(['invoice'])));
    }

    // POST /winery/verifactu/{id}/cancel
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $record = VerifactuRecord::forUser($user->id)->findOrFail($id);

        abort_if(
            $record->submission_status === 'accepted',
            422,
            'No se puede cancelar un registro ya aceptado por la AEAT.'
        );

        $record->update(['submission_status' => 'cancelled']);

        return $this->success($this->format($record->fresh(['invoice'])));
    }

    private function format(VerifactuRecord $r): array
    {
        return [
            'id' => $r->id,
            'invoice_id' => $r->invoice_id,
            'invoice_number' => $r->invoice?->invoice_number,
            'invoice_date' => $r->invoice?->invoice_date?->toDateString(),
            'invoice_amount' => $r->invoice ? (float) $r->invoice->total_amount : null,
            'submission_status' => $r->submission_status,
            'status_label' => $r->status_label,
            'submitted_at' => $r->submitted_at?->toIso8601String(),
            'accepted_at' => $r->accepted_at?->toIso8601String(),
            'rejected_at' => $r->rejected_at?->toIso8601String(),
            'aeat_csv' => $r->aeat_csv,
            'qr_data' => $r->qr_data,
            'response_code' => $r->response_code,
            'response_message' => $r->response_message,
            'error_details' => $r->error_details,
            'created_at' => $r->created_at->toIso8601String(),
        ];
    }
}
