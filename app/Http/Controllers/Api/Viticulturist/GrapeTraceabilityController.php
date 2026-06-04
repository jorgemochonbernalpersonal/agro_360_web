<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrapeTraceabilityController extends Controller
{
    // ─── GET /viticulturist/grape-traceability ───────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Harvest::whereHas('activity', function ($q) use ($user) {
            $q->where('viticulturist_id', $user->id);
        })->with(['activity.plot', 'activity.plotPlanting.grapeVariety']);

        if ($request->filled('campaign_id')) {
            $query->whereHas('activity', function ($q) use ($request) {
                $q->where('campaign_id', $request->campaign_id);
            });
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('destination', 'like', "%{$term}%")
                    ->orWhere('buyer_name', 'like', "%{$term}%")
                    ->orWhere('transport_document_number', 'like', "%{$term}%");
            });
        }

        $items = $query->orderByDesc('created_at')
            ->paginate($this->resolvePerPage($request, 20));

        // Summary stats
        $statsQuery = Harvest::whereHas('activity', function ($q) use ($user, $request) {
            $q->where('viticulturist_id', $user->id);
            if ($request->filled('campaign_id')) {
                $q->where('campaign_id', $request->campaign_id);
            }
        });

        $stats = [
            'total_entries' => (clone $statsQuery)->count(),
            'total_kg' => round((float) (clone $statsQuery)->sum('total_weight'), 2),
            'plots_count' => (clone $statsQuery)->whereHas('activity')
                ->select(DB::raw('COUNT(DISTINCT (SELECT plot_id FROM agricultural_activities WHERE agricultural_activities.id = harvests.activity_id))'))
                ->count(),
            'destinations_count' => (clone $statsQuery)->distinct('destination')->count('destination'),
        ];

        $data = $items->getCollection()->map(fn ($h) => [
            'id' => $h->id,
            'total_weight' => $h->total_weight !== null ? (float) $h->total_weight : null,
            'destination' => $h->destination,
            'buyer_name' => $h->buyer_name,
            'transport_document_number' => $h->transport_document_number,
            'plot_name' => $h->activity?->plot?->name,
            'variety' => $h->activity?->plotPlanting?->grapeVariety?->name,
            'harvest_date' => $h->activity?->activity_date?->toDateString(),
        ]);

        return response()->json([
            'data' => $data,
            'stats' => $stats,
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }
}
