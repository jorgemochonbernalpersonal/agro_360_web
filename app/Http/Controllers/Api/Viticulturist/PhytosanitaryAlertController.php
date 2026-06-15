<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\PhytosanitaryAlertResource;
use App\Models\PhytosanitaryAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhytosanitaryAlertController extends BaseApiController
{
    // ─── GET /viticulturist/phytosanitary-alerts ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'alert_type' => 'nullable|string|max:50',
            'severity' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
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

        return $this->paginated($items, PhytosanitaryAlertResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'required|string|max:100',
            'alert_type' => 'required|string|max:100',
            'severity' => 'required|string|max:50',
            'description' => 'required|string|max:2000',
            'alert_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:alert_date',
        ]);

        $record = \App\Models\PhytosanitaryAlert::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data' => new \App\Http\Resources\Api\PhytosanitaryAlertResource($record),
            'message' => __('Alerta fitosanitaria registrada correctamente.'),
        ], 201);
    }
}
