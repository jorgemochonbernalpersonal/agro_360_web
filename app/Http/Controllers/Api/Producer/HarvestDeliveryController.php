<?php

namespace App\Http\Controllers\Api\Producer;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\HarvestDelivery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HarvestDeliveryController extends BaseApiController
{
    // ─── GET /producer/harvest-deliveries ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $query = HarvestDelivery::with(['plotPlanting.plot', 'plotPlanting.grapeVariety'])
            ->where('viticulturist_id', $userId)
            ->orderByDesc('delivery_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paginated = $query->paginate(50);

        $items = $paginated->getCollection()->map(fn ($d) => [
            'id' => $d->id,
            'delivery_date' => $d->delivery_date,
            'weight_kg' => (float) $d->delivered_kg,
            'price_per_kg' => $d->price_per_kg !== null ? (float) $d->price_per_kg : null,
            'status' => $this->mapStatus($d->status),
            'is_disputed' => $d->status === 'disputed',
            'plot_name' => $d->plotPlanting?->plot?->name,
            'variety' => $d->plotPlanting?->grapeVariety?->name,
            'notes' => $d->notes,
        ]);

        return response()->json([
            'data' => $items,
            'has_more' => $paginated->hasMorePages(),
            'total' => $paginated->total(),
        ]);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /** Map backend status to mobile-friendly values */
    private function mapStatus(?string $status): string
    {
        return match ($status) {
            'matched', 'resolved' => 'paid',
            'disputed' => 'disputed',
            default => 'pending',
        };
    }
}
