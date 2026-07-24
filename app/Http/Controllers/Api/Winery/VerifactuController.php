<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Invoice;
use App\Services\VerifactuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * API móvil de VeriFactu.
 *
 * Opera sobre el MISMO sistema que el panel web (Invoice.sif_* + SifRecord +
 * VerifactuService): no hay estado paralelo. `submit`/`cancel` declaran de
 * verdad ante la AEAT a través del servicio.
 */
class VerifactuController extends BaseApiController
{
    private const STATUSES = [
        'pendiente' => 'Pendiente',
        'aceptado' => 'Aceptada',
        'error' => 'Error',
    ];

    public function __construct(private VerifactuService $verifactu) {}

    // GET /winery/verifactu
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Invoice::forUser($user->id)
            ->whereNotNull('invoice_number')
            ->whereNotIn('status', ['draft'])
            ->with('client')
            ->orderByDesc('invoice_date');

        // El filtro `status` mapea a sif_status (pendiente|aceptado|error).
        if ($request->filled('status')) {
            $status = $request->input('status');
            $query->where('sif_status', $status);
            if ($status === 'pendiente') {
                $query->where('sif_excluded', false);
            }
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items = $query->paginate($perPage);

        $pendingCount = Invoice::forUser($user->id)
            ->whereNotNull('invoice_number')
            ->whereNotIn('status', ['draft'])
            ->where('sif_status', 'pendiente')
            ->where('sif_excluded', false)
            ->count();

        return $this->paginated($items, $items->map(fn ($i) => $this->format($i)), [
            'pending_invoices' => $pendingCount,
            'statuses' => self::STATUSES,
        ]);
    }

    // GET /winery/verifactu/{id}
    public function show(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::forUser($request->user()->id)
            ->with(['client', 'sifRecords'])
            ->findOrFail($id);

        return $this->success($this->format($invoice, includeRecords: true));
    }

    // POST /winery/verifactu/submit — declara una factura ante la AEAT
    public function submit(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|integer|exists:invoices,id',
        ]);

        $invoice = Invoice::forUser($request->user()->id)
            ->whereNotNull('invoice_number')
            ->findOrFail($validated['invoice_id']);

        abort_if($invoice->status === 'draft', 422, 'No se puede declarar una factura en borrador.');
        abort_if($invoice->sif_status === 'aceptado', 422, 'La factura ya ha sido aceptada por la AEAT.');

        $result = $this->verifactu->send($invoice);

        return $this->success([
            'invoice' => $this->format($invoice->fresh(['client', 'sifRecords']), includeRecords: true),
            'success' => $result['success'],
            'csv' => $result['csv'] ?? null,
            'errors' => $result['errors'] ?? [],
        ], $result['success'] ? 200 : 422);
    }

    // POST /winery/verifactu/{id}/cancel — registra una anulación ante la AEAT
    public function cancel(Request $request, int $id): JsonResponse
    {
        $invoice = Invoice::forUser($request->user()->id)->findOrFail($id);

        abort_unless(
            $invoice->sif_status === 'aceptado',
            422,
            'Solo se pueden anular facturas aceptadas por la AEAT.'
        );

        $result = $this->verifactu->cancel($invoice);

        return $this->success([
            'invoice' => $this->format($invoice->fresh(['client', 'sifRecords']), includeRecords: true),
            'success' => $result['success'],
            'errors' => $result['errors'] ?? [],
        ], $result['success'] ? 200 : 422);
    }

    private function format(Invoice $i, bool $includeRecords = false): array
    {
        $data = [
            'id' => $i->id,
            'invoice_id' => $i->id,
            'invoice_number' => $i->invoice_number,
            'invoice_date' => $i->invoice_date?->toDateString(),
            'invoice_amount' => (float) $i->total_amount,
            'client_name' => $i->client?->full_name,
            'sif_status' => $i->sif_status,
            'status_label' => self::STATUSES[$i->sif_status] ?? $i->sif_status,
            'is_verified' => (bool) $i->is_verified_aet,
            'excluded' => (bool) $i->sif_excluded,
            'csv' => $i->sif_uuid,
            'huella' => $i->sif_hash,
            'sent_at' => $i->sif_sent_at?->toIso8601String(),
        ];

        if ($includeRecords) {
            $data['records'] = $i->sifRecords->map(fn ($r) => [
                'id' => $r->id,
                'tipo' => $r->tipo_registro,
                'status' => $r->status,
                'csv' => $r->csv,
                'huella' => $r->huella,
                'error_message' => $r->error_message,
                'created_at' => $r->created_at->toIso8601String(),
            ])->values();
        }

        return $data;
    }
}
