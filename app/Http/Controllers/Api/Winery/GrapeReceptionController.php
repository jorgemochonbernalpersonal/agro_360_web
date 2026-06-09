<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\HarvestResource;
use App\Models\Campaign;
use App\Models\Container;
use App\Models\GrapeReceptionBatch;
use App\Models\Harvest;
use App\Models\PlotPlanting;
use App\Models\User;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrapeReceptionController extends BaseApiController
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

        // ?uninvoiced=1 — exclude harvests already linked to an active (non-cancelled) invoice
        if ($request->boolean('uninvoiced')) {
            $query->whereDoesntHave('invoiceItems', function ($q) {
                $q->where('concept_type', 'harvest')
                    ->whereHas('invoice', fn ($q2) => $q2->where('status', '!=', 'cancelled'));
            });
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $harvests = $query->orderByDesc('harvest_start_date')->paginate($perPage);

        return $this->paginated($harvests, HarvestResource::collection($harvests->items()));
    }

    // ─── GET /winery/grape-receptions/{id} ───────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)
            ->with(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container'])
            ->findOrFail($id);

        return $this->success(new HarvestResource($harvest));
    }

    // ─── POST /winery/grape-receptions ───────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'viticulturist_id' => 'required|integer|exists:users,id',
            'total_weight' => 'required|numeric|min:0.1',
            'harvest_start_date' => 'required|date',
            'vintage_year' => 'required|integer|min:2000|max:'.(now()->year + 1),
            'plot_planting_id' => 'required|integer|exists:plot_plantings,id',
            'container_id' => 'required|integer|exists:containers,id',
            'baume_degree' => 'nullable|numeric|between:0,25',
            'brix_degree' => 'nullable|numeric|between:0,40',
            'ph_level' => 'nullable|numeric|between:2,5',
            'acidity_level' => 'nullable|numeric|between:0,20',
            'price_per_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'harvest_ticket_number' => 'nullable|string|max:100',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        // Validate viticulturist is linked to this winery (commercial gate, not cuaderno gate)
        $isSelf = $user->isProducer() && (int) $validated['viticulturist_id'] === $user->id;
        if (! $isSelf) {
            WineryViticulturist::where('winery_id', $user->id)
                ->where('viticulturist_id', $validated['viticulturist_id'])
                ->firstOrFail();
        }

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }

        // Validate planting belongs to the viticulturist
        // Validate planting belongs to the viticulturist
        $planting = PlotPlanting::whereHas('plot', fn ($q) => $q->where('viticulturist_id', $validated['viticulturist_id'])
        )->findOrFail($validated['plot_planting_id']);

        $harvest = DB::transaction(function () use ($validated, $user, $planting) {
            $campaignYear = (int) $validated['vintage_year'];
            $campaign = Campaign::getOrCreateActiveForYear($user->id, $campaignYear);

            $batch = GrapeReceptionBatch::firstOrCreate([
                'winery_id' => $user->id,
                'viticulturist_id' => $validated['viticulturist_id'],
                'vintage_year' => $campaignYear,
                'plot_planting_id' => $planting->id,
                'campaign_id' => $campaign?->id,
            ], [
                'status' => 'open',
                'total_weight_kg' => 0,
                'designation_of_origin' => $planting->designation_of_origin,
            ]);

            $harvest = Harvest::create([
                'winery_id' => $user->id,
                'batch_id' => $batch->id,
                'plot_planting_id' => $planting->id,
                'harvest_start_date' => $validated['harvest_start_date'],
                'total_weight' => $validated['total_weight'],
                'container_id' => $validated['container_id'],
                'baume_degree' => $validated['baume_degree'] ?? null,
                'brix_degree' => $validated['brix_degree'] ?? null,
                'ph_level' => $validated['ph_level'] ?? null,
                'acidity_level' => $validated['acidity_level'] ?? null,
                'price_per_kg' => $validated['price_per_kg'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'harvest_ticket_number' => $validated['harvest_ticket_number'] ?? null,
                'vehicle_plate' => $validated['vehicle_plate'] ?? null,
                'status' => 'active',
                'vintage' => $campaignYear,
            ]);

            $batch->recalculateTotal();

            return $harvest;
        });

        $harvest->load(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container']);

        return $this->created(new HarvestResource($harvest));
    }

    // ─── PUT /winery/grape-receptions/{id} ───────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $harvest = Harvest::where('winery_id', $user->id)->findOrFail($id);

        $validated = $request->validate([
            'total_weight' => 'sometimes|numeric|min:0.1',
            'harvest_start_date' => 'sometimes|date',
            'container_id' => 'nullable|integer',
            'baume_degree' => 'nullable|numeric|between:0,25',
            'brix_degree' => 'nullable|numeric|between:0,40',
            'ph_level' => 'nullable|numeric|between:2,5',
            'acidity_level' => 'nullable|numeric|between:0,20',
            'price_per_kg' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($harvest, $validated) {
            $harvest->update($validated);
            if ($harvest->batch) {
                $harvest->batch->recalculateTotal();
            }
        });

        $harvest->load(['batch.viticulturist', 'plotPlanting.grapeVariety', 'container']);

        return $this->success(new HarvestResource($harvest));
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

        return $this->deleted(__('Recepción eliminada correctamente.'));
    }

    // ─── GET /winery/viticulturists ────────────────────────────────────────────

    public function viticulturists(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        // Solo viticultores con parcelas activas que tengan plantaciones activas
        $viticulturistIds = WineryViticulturist::where('winery_id', $user->id)
            ->whereHas('viticulturist.plots', fn ($q) => $q->where('active', true)
                ->whereHas('plantings', fn ($p) => $p->where('status', 'active'))
            )
            ->pluck('viticulturist_id');

        $viticulturists = User::whereIn('id', $viticulturistIds)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return $this->success($viticulturists);
    }
}
