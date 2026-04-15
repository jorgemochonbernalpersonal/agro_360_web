<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PhytosanitaryAlertResource;
use App\Models\PhytosanitaryAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhytosanitaryAlertController extends Controller
{
    // ─── GET /viticulturist/phytosanitary-alerts ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'alert_type' => 'nullable|string|max:50',
            'severity'   => 'nullable|string|max:50',
            'search'     => 'nullable|string|max:100',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $query = PhytosanitaryAlert::active()
            ->where('viticulturist_id', $user->id)
            ->orderByDesc('alert_date');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('alert_type')) {
            $query->where('alert_type', $request->alert_type);
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PhytosanitaryAlertResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
