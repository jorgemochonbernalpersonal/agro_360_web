<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryCampaignController extends Controller
{
    /**
     * Campañas de los viticultores vinculados a la bodega (solo lectura).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $query = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->with('viticulturist')
            ->orderByDesc('year')
            ->orderByDesc('start_date');

        if ($request->filled('viticulturist_id')) {
            $query->where('viticulturist_id', $request->integer('viticulturist_id'));
        }
        if ($request->filled('year')) {
            $query->forYear($request->integer('year'));
        }
        if ($request->boolean('active_only', false)) {
            $query->active();
        }

        $perPage   = min($request->integer('per_page', 20), 100);
        $campaigns = $query->paginate($perPage);

        return response()->json([
            'data' => $campaigns->map(fn ($c) => $this->format($c)),
            'meta' => [
                'total'        => $campaigns->total(),
                'per_page'     => $campaigns->perPage(),
                'current_page' => $campaigns->currentPage(),
                'last_page'    => $campaigns->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $campaign = Campaign::whereIn('viticulturist_id', $viticulturistIds)
            ->with('viticulturist')
            ->findOrFail($id);

        return response()->json(['data' => $this->format($campaign)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $validated = $request->validate([
            'viticulturist_id' => 'required|integer|in:' . $viticulturistIds->implode(','),
            'name'             => 'required|string|max:255',
            'year'             => 'required|integer|min:2000|max:' . (now()->year + 2),
            'start_date'       => 'nullable|date',
            'end_date'         => 'nullable|date|after_or_equal:start_date',
            'active'           => 'nullable|boolean',
            'description'      => 'nullable|string|max:2000',
        ]);

        $campaign = Campaign::create($validated);
        $campaign->load('viticulturist');

        return response()->json([
            'data'    => $this->format($campaign),
            'message' => 'Campaña creada correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $campaign = Campaign::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        $validated = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'year'        => 'sometimes|integer|min:2000|max:' . (now()->year + 2),
            'start_date'  => 'sometimes|nullable|date',
            'end_date'    => 'sometimes|nullable|date|after_or_equal:start_date',
            'active'      => 'sometimes|boolean',
            'description' => 'sometimes|nullable|string|max:2000',
        ]);

        $campaign->update($validated);
        $campaign->load('viticulturist');

        return response()->json(['data' => $this->format($campaign)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $campaign = Campaign::whereIn('viticulturist_id', $viticulturistIds)->findOrFail($id);

        abort_if($campaign->locked_at !== null, 422, 'No se puede eliminar una campaña bloqueada.');

        $campaign->delete();

        return response()->json(['message' => 'Campaña eliminada correctamente.']);
    }

    private function format(Campaign $c): array
    {
        return [
            'id'                      => $c->id,
            'name'                    => $c->name,
            'year'                    => $c->year,
            'viticulturist_id'        => $c->viticulturist_id,
            'viticulturist_name'      => $c->viticulturist?->name,
            'start_date'              => $c->start_date?->toDateString(),
            'end_date'                => $c->end_date?->toDateString(),
            'active'                  => $c->active,
            'description'             => $c->description,
            'mid_validation_signed'   => $c->mid_validation_signed,
            'final_validation_signed' => $c->final_validation_signed,
            'locked_at'               => $c->locked_at?->toIso8601String(),
            'created_at'              => $c->created_at->toIso8601String(),
        ];
    }
}
