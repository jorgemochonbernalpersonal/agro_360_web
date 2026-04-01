<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\HarvestResource;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrapeReceptionController extends Controller
{
    // ─── GET /winery/grape-receptions ────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = Harvest::where('winery_id', $user->id)
            ->with(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container']);

        if ($request->filled('viticulturist_id')) {
            $viticulturistId = (int) $request->input('viticulturist_id');
            $query->whereHas('batch', fn ($q) => $q->where('viticulturist_id', $viticulturistId));
        }

        $harvests = $query->orderByDesc('harvest_start_date')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => HarvestResource::collection($harvests->items()),
            'meta' => [
                'total'        => $harvests->total(),
                'current_page' => $harvests->currentPage(),
                'last_page'    => $harvests->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/grape-receptions/{id} ───────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)
            ->with(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container'])
            ->findOrFail($id);

        return response()->json(['data' => new HarvestResource($harvest)]);
    }

    // ─── POST /winery/grape-receptions ───────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'viticulturist_id'     => 'required|integer|exists:users,id',
            'total_weight'         => 'required|numeric|min:0.1',
            'harvest_start_date'   => 'required|date',
            'container_id'         => 'nullable|integer|exists:containers,id',
            'baume_degree'         => 'nullable|numeric|between:0,25',
            'brix_degree'          => 'nullable|numeric|between:0,40',
            'ph_level'             => 'nullable|numeric|between:2,5',
            'acidity_level'        => 'nullable|numeric|between:0,20',
            'price_per_kg'         => 'nullable|numeric|min:0',
            'notes'                => 'nullable|string|max:1000',
            'harvest_ticket_number' => 'nullable|string|max:100',
            'vehicle_plate'        => 'nullable|string|max:20',
        ]);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        $harvest = DB::transaction(function () use ($validated, $user) {
            $campaignYear = now()->year;

            // Find or create batch for this viticulturist + winery + year
            $batch = GrapeReceptionBatch::firstOrCreate([
                'winery_id'        => $user->id,
                'viticulturist_id' => $validated['viticulturist_id'],
                'vintage_year'     => $campaignYear,
            ], [
                'status'           => 'open',
                'total_weight_kg'  => 0,
            ]);

            $harvest = Harvest::create([
                'winery_id'            => $user->id,
                'batch_id'             => $batch->id,
                'harvest_start_date'   => $validated['harvest_start_date'],
                'total_weight'         => $validated['total_weight'],
                'container_id'         => $validated['container_id'] ?? null,
                'baume_degree'         => $validated['baume_degree'] ?? null,
                'brix_degree'          => $validated['brix_degree'] ?? null,
                'ph_level'             => $validated['ph_level'] ?? null,
                'acidity_level'        => $validated['acidity_level'] ?? null,
                'price_per_kg'         => $validated['price_per_kg'] ?? null,
                'notes'                => $validated['notes'] ?? null,
                'harvest_ticket_number' => $validated['harvest_ticket_number'] ?? null,
                'vehicle_plate'        => $validated['vehicle_plate'] ?? null,
                'status'               => 'active',
                'vintage'              => $campaignYear,
            ]);

            $batch->recalculateTotal();

            return $harvest;
        });

        $harvest->load(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container']);

        return response()->json(['data' => new HarvestResource($harvest)], 201);
    }

    // ─── PUT /winery/grape-receptions/{id} ───────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'total_weight'       => 'sometimes|numeric|min:0.1',
            'harvest_start_date' => 'sometimes|date',
            'container_id'       => 'nullable|integer',
            'baume_degree'       => 'nullable|numeric|between:0,25',
            'brix_degree'        => 'nullable|numeric|between:0,40',
            'ph_level'           => 'nullable|numeric|between:2,5',
            'acidity_level'      => 'nullable|numeric|between:0,20',
            'price_per_kg'       => 'nullable|numeric|min:0',
            'notes'              => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($harvest, $validated) {
            $harvest->update($validated);
            if ($harvest->batch) {
                $harvest->batch->recalculateTotal();
            }
        });

        $harvest->load(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container']);

        return response()->json(['data' => new HarvestResource($harvest)]);
    }

    // ─── DELETE /winery/grape-receptions/{id} ────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)->findOrFail($id);

        DB::transaction(function () use ($harvest) {
            $batch = $harvest->batch;
            $harvest->delete();
            if ($batch) {
                $batch->recalculateTotal();
            }
        });

        return response()->json(['message' => 'Recepción eliminada correctamente.']);
    }

    // ─── GET /winery/viticulturists ────────────────────────────────────────────

    public function viticulturists(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $viticulturists]);
    }
}
