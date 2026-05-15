<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\OfficialReportResource;
use App\Models\OfficialReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfficialReportController extends Controller
{
    // ─── GET /viticulturist/official-reports ─────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'report_type' => 'nullable|string',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = OfficialReport::forUser($user->id)
            ->valid()
            ->recent();

        if ($request->filled('report_type')) {
            $query->ofType($request->report_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('report_type', 'like', "%{$term}%")
                  ->orWhere('verification_code', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => OfficialReportResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
