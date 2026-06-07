<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\PlannedWorkResource;
use App\Models\PlannedWork;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlannedWorkController extends BaseApiController
{
    // ─── GET /viticulturist/planned-works ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|min:1',
            'plot_id' => 'nullable|integer|min:1',
            'category' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'priority' => 'nullable|string|max:50',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = PlannedWork::where('viticulturist_id', $user->id)
            ->with(['plot', 'campaign'])
            ->orderByDesc('planned_date');

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', (int) $request->plot_id);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, PlannedWorkResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'plot_id' => 'nullable|integer|exists:plots,id',
            'category' => 'required|string|max:100',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'planned_date' => 'required|date',
            'priority' => 'nullable|string|max:50',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
        ]);

        if (isset($validated['plot_id'])) {
            \App\Models\Plot::where('viticulturist_id', $user->id)->findOrFail($validated['plot_id']);
        }

        $record = \App\Models\PlannedWork::create([...$validated, 'viticulturist_id' => $user->id, 'status' => $validated['status'] ?? 'pendiente', 'priority' => $validated['priority'] ?? 'media']);

        return response()->json([
            'data' => new \App\Http\Resources\Api\PlannedWorkResource($record),
            'message' => __('Trabajo planeado registrado correctamente.'),
        ], 201);
    }
}
