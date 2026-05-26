<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\WineryYieldForecast;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class YieldForecastController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = WineryYieldForecast::forWinery($user->id)
            ->with(['viticulturist', 'plotPlanting', 'campaign'])
            ->orderByDesc('estimation_date');

        if ($request->filled('vintage_year')) {
            $query->forVintage($request->integer('vintage_year'));
        }
        if ($request->filled('viticulturist_id')) {
            $query->where('viticulturist_id', $request->integer('viticulturist_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($f) => $this->format($f)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $forecast = WineryYieldForecast::forWinery($user->id)
            ->with(['viticulturist', 'plotPlanting', 'campaign'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($forecast)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'viticulturist_id' => 'required|integer|exists:users,id',
            'plot_planting_id' => 'required|integer|exists:plot_plantings,id',
            'campaign_id'      => 'required|integer|exists:campaigns,id',
            'vintage_year'     => 'required|integer|min:1900|max:2100',
            'estimated_kg'     => 'required|numeric|min:0',
            'estimation_date'  => 'required|date',
            'status'           => 'nullable|string|in:draft,confirmed',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $validated['status']    ??= 'draft';
        $validated['winery_id']   = $user->id;

        $forecast = WineryYieldForecast::create($validated);
        $forecast->load(['viticulturist', 'plotPlanting', 'campaign']);

        return response()->json([
            'data'    => $this->format($forecast),
            'message' => __('Previsión de rendimiento creada correctamente.'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $forecast = WineryYieldForecast::forWinery($user->id)->findOrFail($id);

        $validated = $request->validate([
            'viticulturist_id' => 'sometimes|nullable|integer|exists:users,id',
            'plot_planting_id' => 'sometimes|nullable|integer|exists:plot_plantings,id',
            'campaign_id'      => 'sometimes|nullable|integer|exists:campaigns,id',
            'vintage_year'     => 'sometimes|integer|min:1900|max:2100',
            'estimated_kg'     => 'sometimes|numeric|min:0',
            'estimation_date'  => 'sometimes|date',
            'status'           => 'sometimes|string|in:draft,confirmed',
            'notes'            => 'sometimes|nullable|string|max:1000',
        ]);

        $forecast->update($validated);
        $forecast->load(['viticulturist', 'plotPlanting', 'campaign']);

        return response()->json(['data' => $this->format($forecast)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $forecast = WineryYieldForecast::forWinery($user->id)->findOrFail($id);
        $forecast->delete();

        return response()->json(['message' => __('Previsión eliminada correctamente.')]);
    }

    private function format(WineryYieldForecast $f): array
    {
        return [
            'id'                   => $f->id,
            'viticulturist_id'     => $f->viticulturist_id,
            'viticulturist_name'   => $f->viticulturist?->name,
            'plot_planting_id'     => $f->plot_planting_id,
            'campaign_id'          => $f->campaign_id,
            'campaign_name'        => $f->campaign?->name,
            'vintage_year'         => $f->vintage_year,
            'estimated_kg'         => (float) $f->estimated_kg,
            'estimation_date'      => $f->estimation_date?->toDateString(),
            'status'               => $f->status,
            'is_confirmed'         => $f->isConfirmed(),
            'received_kg'          => $f->receivedKg(),
            'execution_percentage' => $f->executionPercentage(),
            'exceeds_forecast'     => $f->exceedsForecast(),
            'notes'                => $f->notes,
            'created_at'           => $f->created_at->toIso8601String(),
        ];
    }
}
