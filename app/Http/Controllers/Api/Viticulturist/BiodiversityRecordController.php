<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BiodiversityRecordResource;
use App\Models\BiodiversityRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiodiversityRecordController extends Controller
{
    // ─── GET /viticulturist/biodiversity-records ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'plot_id'     => 'nullable|integer',
            'record_type' => 'nullable|string',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = BiodiversityRecord::where('viticulturist_id', $user->id)
            ->with(['plot'])
            ->orderByDesc('record_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('species', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => BiodiversityRecordResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
