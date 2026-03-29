<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ActivityResource;
use App\Models\AgriculturalActivity;
use App\Models\Campaign;
use App\Models\CulturalWork;
use App\Models\Fertilization;
use App\Models\Irrigation;
use App\Models\Observation;
use App\Models\PhytosanitaryTreatment;
use App\Models\Plot;
use App\Models\PostHarvestTreatment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotebookController extends Controller
{
    // ─── Relaciones a cargar según tipo ───────────────────────────────────────

    private const DETAIL_RELATIONS = [
        'phytosanitary' => 'phytosanitaryTreatment',
        'fertilization' => 'fertilization',
        'irrigation'    => 'irrigation',
        'cultural'      => 'culturalWork',
        'pruning'       => 'culturalWork',
        'observation'   => 'observation',
        'post_harvest'  => 'postHarvestTreatment',
    ];

    // ─── GET /viticulturist/notebook ──────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'type'        => 'nullable|string|in:phytosanitary,fertilization,irrigation,cultural,observation,harvest,pruning,phenology,post_harvest',
            'plot_id'     => 'nullable|integer|min:1',
            'campaign_id' => 'nullable|integer|min:1',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = AgriculturalActivity::forViticulturist($user->id)
            ->with(['plot', 'campaign']);

        if ($request->filled('type')) {
            $query->ofType($request->type);
        }
        if ($request->filled('plot_id')) {
            $query->forPlot((int) $request->plot_id);
        }
        if ($request->filled('campaign_id')) {
            $query->forCampaign((int) $request->campaign_id);
        }

        $activities = $query->orderByDesc('activity_date')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'data' => ActivityResource::collection($activities->items()),
            'meta' => [
                'total'        => $activities->total(),
                'current_page' => $activities->currentPage(),
                'last_page'    => $activities->lastPage(),
            ],
        ]);
    }

    // ─── GET /viticulturist/notebook/{id} ─────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $activity = AgriculturalActivity::forViticulturist($user->id)
            ->findOrFail($id);

        $this->loadDetails($activity);

        return response()->json(['data' => new ActivityResource($activity)]);
    }

    // ─── POST /viticulturist/notebook ─────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate(array_merge(
            $this->baseRules(),
            $this->detailRules($request->activity_type)
        ));

        // Ownership check
        Plot::where('viticulturist_id', $user->id)->findOrFail($validated['plot_id']);

        // Auto-assign active campaign if not provided
        if (empty($validated['campaign_id'])) {
            $campaign = Campaign::getOrCreateActiveForYear($user->id);
            $validated['campaign_id'] = $campaign?->id;
        } else {
            Campaign::forViticulturist($user->id)->findOrFail($validated['campaign_id']);
        }

        $activity = AgriculturalActivity::create([
            'activity_type'      => $validated['activity_type'],
            'plot_id'            => $validated['plot_id'],
            'activity_date'      => $validated['activity_date'],
            'campaign_id'        => $validated['campaign_id'],
            'phenological_stage' => $validated['phenological_stage'] ?? null,
            'weather_conditions' => $validated['weather_conditions'] ?? null,
            'temperature'        => $validated['temperature'] ?? null,
            'notes'              => $validated['notes'] ?? null,
            'viticulturist_id'   => $user->id,
        ]);

        $this->createDetails($activity, $validated);

        $this->loadDetails($activity);

        return response()->json(['data' => new ActivityResource($activity)], 201);
    }

    // ─── PUT /viticulturist/notebook/{id} ─────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $activity = AgriculturalActivity::forViticulturist($user->id)->findOrFail($id);

        if ($activity->is_locked) {
            return response()->json(['message' => 'Esta actividad está bloqueada y no puede editarse.'], 422);
        }

        $validated = $request->validate(array_merge(
            $this->baseUpdateRules(),
            $this->detailRules($activity->activity_type)
        ));

        $activity->update(array_intersect_key($validated, array_flip([
            'activity_date', 'phenological_stage', 'weather_conditions', 'temperature', 'notes',
        ])));

        $this->updateDetails($activity, $validated);

        $this->loadDetails($activity->fresh(['plot', 'campaign']));

        return response()->json(['data' => new ActivityResource($activity->fresh(['plot', 'campaign']))]);
    }

    // ─── DELETE /viticulturist/notebook/{id} ──────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $activity = AgriculturalActivity::forViticulturist($user->id)->findOrFail($id);

        if ($activity->is_locked) {
            return response()->json(['message' => 'Esta actividad está bloqueada y no puede eliminarse.'], 422);
        }

        $activity->delete();

        return response()->json(['message' => 'Actividad eliminada correctamente.']);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function baseRules(): array
    {
        return [
            'activity_type'      => 'required|string|in:phytosanitary,fertilization,irrigation,cultural,observation,harvest,pruning,phenology,post_harvest',
            'plot_id'            => 'required|integer',
            'activity_date'      => 'required|date',
            'campaign_id'        => 'nullable|integer',
            'phenological_stage' => 'nullable|string|max:100',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature'        => 'nullable|numeric|between:-20,60',
            'notes'              => 'nullable|string|max:2000',
        ];
    }

    private function baseUpdateRules(): array
    {
        return [
            'activity_date'      => 'sometimes|date',
            'phenological_stage' => 'nullable|string|max:100',
            'weather_conditions' => 'nullable|string|max:255',
            'temperature'        => 'nullable|numeric|between:-20,60',
            'notes'              => 'nullable|string|max:2000',
        ];
    }

    private function detailRules(?string $type): array
    {
        return match ($type) {
            'phytosanitary' => [
                'product_id'              => ['required', 'integer', \Illuminate\Validation\Rule::exists('phytosanitary_products', 'id')->where(fn ($q) => $q->where(fn ($q2) => $q2->whereNull('user_id')->orWhere('user_id', request()->user()?->id)))],
                'pest_id'                 => 'nullable|integer|exists:pests,id',
                'dose_per_hectare'        => 'nullable|numeric|min:0',
                'total_dose'              => 'nullable|numeric|min:0',
                'area_treated'            => 'nullable|numeric|min:0',
                'application_method'      => 'nullable|string|max:100',
                'treatment_justification' => 'nullable|string|max:500',
                'applicator_ropo_number'  => 'nullable|string|max:50',
                'reentry_period_days'     => 'nullable|integer|min:0',
            ],
            'fertilization' => [
                'fertilizer_type'   => 'nullable|string|max:100',
                'fertilizer_name'   => 'nullable|string|max:255',
                'quantity'          => 'nullable|numeric|min:0',
                'application_method'=> 'nullable|string|max:100',
                'area_applied'      => 'nullable|numeric|min:0',
                'nitrogen_uf'       => 'nullable|numeric|min:0',
                'phosphorus_uf'     => 'nullable|numeric|min:0',
                'potassium_uf'      => 'nullable|numeric|min:0',
            ],
            'irrigation' => [
                'water_volume'          => 'nullable|numeric|min:0',
                'water_volume_unit'     => 'nullable|string|in:L,m3',
                'irrigation_method'     => 'nullable|string|max:100',
                'duration_minutes'      => 'nullable|integer|min:0',
                'is_fertirrigation'     => 'boolean',
                'fertilizer_product'    => 'nullable|string|max:255',
                'fertilizer_dose_per_ha'=> 'nullable|numeric|min:0',
            ],
            'cultural' => [
                'work_type'         => 'nullable|string|max:100',
                'hours_worked'      => 'nullable|numeric|min:0',
                'workers_count'     => 'nullable|integer|min:0',
                'residue_management'=> 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
                'description'       => 'nullable|string|max:1000',
            ],
            'pruning' => [
                'pruning_type'                => 'nullable|string|max:100',
                'productive_buds_per_hectare' => 'nullable|integer|min:0',
                'hours_worked'                => 'nullable|numeric|min:0',
                'workers_count'               => 'nullable|integer|min:0',
                'residue_management'          => 'nullable|string|in:triturado_incorporado,triturado_superficie,retirado,quemado,otro',
            ],
            'observation' => [
                'pest_id'                   => 'nullable|integer|exists:pests,id',
                'observation_type'          => 'nullable|string|max:100',
                'description'               => 'nullable|string|max:2000',
                'severity'                  => 'nullable|string|max:50',
                'affected_area_percentage'  => 'nullable|numeric|min:0|max:100',
                'threshold_exceeded'        => 'boolean',
                'follow_up_date'            => 'nullable|date',
                'action_taken'              => 'nullable|string|max:500',
            ],
            'post_harvest' => [
                'application_type'        => 'required|string|in:copper_treatment,sulfur_treatment,wound_sealing,foliar_application,trunk_treatment,other',
                'product_id'              => 'nullable|integer|exists:phytosanitary_products,id',
                'treated_area_ha'         => 'nullable|numeric|min:0',
                'dose_per_hectare'        => 'nullable|numeric|min:0',
                'water_volume_liters'     => 'nullable|numeric|min:0',
                'reentry_interval_hours'  => 'nullable|integer|min:0|max:168',
            ],
            default => [],
        };
    }

    private function createDetails(AgriculturalActivity $activity, array $data): void
    {
        match ($data['activity_type']) {
            'phytosanitary' => PhytosanitaryTreatment::create([
                'activity_id'             => $activity->id,
                'product_id'              => $data['product_id'],
                'pest_id'                 => $data['pest_id'] ?? null,
                'dose_per_hectare'        => $data['dose_per_hectare'] ?? null,
                'total_dose'              => $data['total_dose'] ?? null,
                'area_treated'            => $data['area_treated'] ?? null,
                'application_method'      => $data['application_method'] ?? null,
                'treatment_justification' => $data['treatment_justification'] ?? null,
                'applicator_ropo_number'  => $data['applicator_ropo_number'] ?? null,
                'reentry_period_days'     => $data['reentry_period_days'] ?? null,
            ]),
            'fertilization' => Fertilization::create([
                'activity_id'       => $activity->id,
                'fertilizer_type'   => $data['fertilizer_type'] ?? null,
                'fertilizer_name'   => $data['fertilizer_name'] ?? null,
                'quantity'          => $data['quantity'] ?? null,
                'application_method'=> $data['application_method'] ?? null,
                'area_applied'      => $data['area_applied'] ?? null,
                'nitrogen_uf'       => $data['nitrogen_uf'] ?? null,
                'phosphorus_uf'     => $data['phosphorus_uf'] ?? null,
                'potassium_uf'      => $data['potassium_uf'] ?? null,
            ]),
            'irrigation' => Irrigation::create([
                'activity_id'           => $activity->id,
                'water_volume'          => $data['water_volume'] ?? null,
                'water_volume_unit'     => $data['water_volume_unit'] ?? null,
                'irrigation_method'     => $data['irrigation_method'] ?? null,
                'duration_minutes'      => $data['duration_minutes'] ?? null,
                'is_fertirrigation'     => $data['is_fertirrigation'] ?? false,
                'fertilizer_product'    => $data['fertilizer_product'] ?? null,
                'fertilizer_dose_per_ha'=> $data['fertilizer_dose_per_ha'] ?? null,
            ]),
            'cultural' => CulturalWork::create([
                'activity_id'       => $activity->id,
                'work_type'         => $data['work_type'] ?? null,
                'hours_worked'      => $data['hours_worked'] ?? null,
                'workers_count'     => $data['workers_count'] ?? null,
                'residue_management'=> $data['residue_management'] ?? null,
                'description'       => $data['description'] ?? null,
            ]),
            'pruning' => CulturalWork::create([
                'activity_id'                 => $activity->id,
                'work_type'                   => 'pruning',
                'pruning_type'                => $data['pruning_type'] ?? null,
                'productive_buds_per_hectare' => $data['productive_buds_per_hectare'] ?? null,
                'hours_worked'                => $data['hours_worked'] ?? null,
                'workers_count'               => $data['workers_count'] ?? null,
                'residue_management'          => $data['residue_management'] ?? null,
            ]),
            'observation' => Observation::create([
                'activity_id'               => $activity->id,
                'pest_id'                   => $data['pest_id'] ?? null,
                'observation_type'          => $data['observation_type'] ?? null,
                'description'               => $data['description'] ?? null,
                'severity'                  => $data['severity'] ?? null,
                'affected_area_percentage'  => $data['affected_area_percentage'] ?? null,
                'threshold_exceeded'        => $data['threshold_exceeded'] ?? false,
                'follow_up_date'            => $data['follow_up_date'] ?? null,
                'action_taken'              => $data['action_taken'] ?? null,
            ]),
            'post_harvest' => PostHarvestTreatment::create([
                'activity_id'            => $activity->id,
                'application_type'       => $data['application_type'],
                'product_id'             => $data['product_id'] ?? null,
                'treated_area_ha'        => $data['treated_area_ha'] ?? null,
                'dose_per_hectare'       => $data['dose_per_hectare'] ?? null,
                'water_volume_liters'    => $data['water_volume_liters'] ?? null,
                'reentry_interval_hours' => $data['reentry_interval_hours'] ?? null,
            ]),
            default => null, // phenology, harvest — sin sub-modelo en la API por ahora
        };
    }

    private function updateDetails(AgriculturalActivity $activity, array $data): void
    {
        $detailFields = array_diff_key($data, array_flip([
            'activity_date', 'phenological_stage', 'weather_conditions', 'temperature', 'notes',
        ]));

        if (empty($detailFields)) {
            return;
        }

        match ($activity->activity_type) {
            'phytosanitary' => $activity->phytosanitaryTreatment?->update($detailFields),
            'fertilization' => $activity->fertilization?->update($detailFields),
            'irrigation'    => $activity->irrigation?->update($detailFields),
            'cultural', 'pruning' => $activity->culturalWork?->update($detailFields),
            'observation'   => $activity->observation?->update($detailFields),
            'post_harvest'  => $activity->postHarvestTreatment?->update($detailFields),
            default         => null,
        };
    }

    private function loadDetails(AgriculturalActivity $activity): void
    {
        $relation = self::DETAIL_RELATIONS[$activity->activity_type] ?? null;

        if ($relation) {
            $activity->load(['plot', 'campaign', $relation]);
        } else {
            $activity->load(['plot', 'campaign']);
        }
    }
}
