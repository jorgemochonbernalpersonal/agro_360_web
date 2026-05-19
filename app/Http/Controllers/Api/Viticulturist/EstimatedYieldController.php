<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EstimatedYieldResource;
use App\Models\Campaign;
use App\Models\EstimatedYield;
use App\Models\PlotPlanting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EstimatedYieldController extends Controller
{
    // ─── GET /viticulturist/notebook/estimated-yields ─────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'plot_id'          => 'nullable|integer|min:1',
            'plot_planting_id' => 'nullable|integer|min:1',
            'campaign_id'      => 'nullable|integer|min:1',
            'status'           => 'nullable|string|in:draft,confirmed',
            'per_page'         => 'nullable|integer|min:1|max:100',
        ]);

        $query = EstimatedYield::whereHas('plotPlanting', fn ($q) =>
                $q->whereHas('plot', fn ($q2) =>
                    $q2->where('viticulturist_id', $user->id)
                )
            )
            ->where('active', true)
            ->with(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign'])
            ->orderByDesc('estimation_date');

        if ($request->filled('plot_id')) {
            $query->whereHas('plotPlanting', fn ($q) =>
                $q->where('plot_id', (int) $request->plot_id)
            );
        }

        if ($request->filled('plot_planting_id')) {
            $query->where('plot_planting_id', (int) $request->plot_planting_id);
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $yields = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => EstimatedYieldResource::collection($yields->items()),
            'meta' => [
                'total'        => $yields->total(),
                'current_page' => $yields->currentPage(),
                'last_page'    => $yields->lastPage(),
                'has_more'     => $yields->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/notebook/estimated-yields ────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $this->normalizeCampaignId($request);

        $validated = $request->validate([
            'plot_planting_id'            => 'required|integer|min:1',
            'estimation_date'             => 'required|date',
            'estimation_round'            => 'required|integer|in:1,2,3,4',
            'campaign_id'                 => 'nullable|integer|min:1',
            'estimation_method'           => 'nullable|string|max:100',
            'estimated_yield_per_hectare' => 'nullable|numeric|min:0',
            'notes'                       => 'nullable|string|max:2000',
            // Muestreo de campo
            'thumbs_per_vine'             => 'nullable|integer|min:0',
            'bunches_per_plant'           => 'nullable|numeric|min:0',
            'bunch_weight_grams'          => 'nullable|numeric|min:0',
            'total_plants_sampled'        => 'nullable|integer|min:0',
            'sampling_area_pct'           => 'nullable|numeric|min:0|max:100',
            'health_percentage'           => 'nullable|numeric|min:0|max:100',
            'health_status'               => 'nullable|string|in:' . implode(',', array_keys(EstimatedYield::HEALTH_STATUSES)),
            'potential_alcohol'           => 'nullable|numeric|min:0|max:25',
            'vintage'                     => 'nullable|integer|min:1900|max:2100',
        ]);

        // Verificar que la plantación pertenece al viticulturist
        PlotPlanting::whereHas('plot', fn ($q) =>
            $q->where('viticulturist_id', $user->id)
        )->findOrFail($validated['plot_planting_id']);

        // Auto-asignar campaña activa si no se proporciona
        if (empty($validated['campaign_id'])) {
            $campaign = Campaign::getOrCreateActiveForYear($user->id);
            $validated['campaign_id'] = $campaign?->id;
        } else {
            Campaign::forViticulturist($user->id)->findOrFail($validated['campaign_id']);
        }

        $data = [
            ...$validated,
            'estimated_by' => $user->id,
            'status'       => 'draft',
            'active'       => true,
            'vintage'      => $validated['vintage'] ?? now()->year,
        ];

        try {
            $yield = EstimatedYield::create($data);
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            $roundLabel = EstimatedYield::ROUNDS[$validated['estimation_round']] ?? "ronda {$validated['estimation_round']}";
            return response()->json([
                'message' => "Ya existe una estimación de {$roundLabel} para esta plantación en la campaña activa. Edita la existente o elige otra ronda.",
                'error'   => 'duplicate_round',
            ], 409);
        }

        $yield->load(['plotPlanting.plot', 'plotPlanting.grapeVariety', 'campaign']);

        return response()->json(['data' => new EstimatedYieldResource($yield)], 201);
    }
}
