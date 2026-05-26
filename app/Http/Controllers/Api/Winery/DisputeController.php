<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\HarvestDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    /**
     * List all disputed deliveries for this winery's harvests.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = HarvestDelivery::whereHas('harvest', fn ($q) => $q->where('winery_id', $user->id))
            ->with(['viticulturist', 'harvest', 'plotPlanting.plot'])
            ->orderByDesc('dispute_submitted_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->whereIn('status', ['disputed', 'resolved']);
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($d) => $this->format($d)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
            ],
        ]);
    }

    /**
     * Show a single delivery dispute.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $delivery = HarvestDelivery::whereHas('harvest', fn ($q) => $q->where('winery_id', $user->id))
            ->with(['viticulturist', 'harvest', 'plotPlanting.plot'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($delivery)]);
    }

    /**
     * Resolve a dispute (winery side).
     */
    public function resolve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $delivery = HarvestDelivery::whereHas('harvest', fn ($q) => $q->where('winery_id', $user->id))
            ->findOrFail($id);

        abort_unless($delivery->isDisputed(), 422, 'Solo se pueden resolver entregas en disputa.');

        $validated = $request->validate([
            'dispute_resolution_note' => 'required|string|max:2000',
        ]);

        $delivery->update([
            'dispute_resolution_note' => $validated['dispute_resolution_note'],
            'dispute_resolved_at'     => now(),
            'status'                  => 'resolved',
        ]);

        $delivery->load(['viticulturist', 'harvest', 'plotPlanting.plot']);

        return response()->json([
            'data'    => $this->format($delivery),
            'message' => __('Disputa resuelta correctamente.'),
        ]);
    }

    private function format(HarvestDelivery $d): array
    {
        return [
            'id'                       => $d->id,
            'viticulturist_id'         => $d->viticulturist_id,
            'viticulturist_name'       => $d->viticulturist?->name,
            'harvest_id'               => $d->harvest_id,
            'plot_planting_id'         => $d->plot_planting_id,
            'vintage_year'             => $d->vintage_year,
            'delivery_date'            => $d->delivery_date?->toDateString(),
            'ticket_number'            => $d->ticket_number,
            'delivered_kg'             => (float) $d->delivered_kg,
            'discrepancy_kg'           => $d->discrepancy_kg !== null ? (float) $d->discrepancy_kg : null,
            'discrepancy_percentage'   => $d->discrepancyPercentage(),
            'status'                   => $d->status,
            'dispute_note'             => $d->dispute_note,
            'dispute_submitted_at'     => $d->dispute_submitted_at?->toIso8601String(),
            'dispute_resolution_note'  => $d->dispute_resolution_note,
            'dispute_resolved_at'      => $d->dispute_resolved_at?->toIso8601String(),
            // Calidad
            'baume_degree'             => $d->baume_degree !== null ? (float) $d->baume_degree : null,
            'brix_degree'              => $d->brix_degree !== null ? (float) $d->brix_degree : null,
            'potential_alcohol'        => $d->potential_alcohol !== null ? (float) $d->potential_alcohol : null,
            'acidity_level'            => $d->acidity_level !== null ? (float) $d->acidity_level : null,
            'ph_level'                 => $d->ph_level !== null ? (float) $d->ph_level : null,
            'disqualified'             => $d->disqualified,
            'disqualified_reason'      => $d->disqualified_reason,
        ];
    }
}
