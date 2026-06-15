<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Viticulturist\IndexNotebookRequest;
use App\Http\Requests\Api\Viticulturist\IndexTypedNotebookRequest;
use App\Http\Requests\Api\Viticulturist\StoreNotebookRequest;
use App\Http\Requests\Api\Viticulturist\UpdateNotebookRequest;
use App\Http\Requests\Api\Viticulturist\ViticulturistApiRequest;
use App\Http\Resources\Api\ActivityResource;
use App\Http\Resources\Api\MobileNotebookResource;
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
use Illuminate\Support\Facades\Cache;

class NotebookController extends BaseApiController
{
    // ─── Relaciones a cargar según tipo ───────────────────────────────────────

    private const DETAIL_RELATIONS = [
        'phytosanitary' => 'phytosanitaryTreatment',
        'fertilization' => 'fertilization',
        'irrigation' => 'irrigation',
        'cultural' => 'culturalWork',
        'pruning' => 'culturalWork',
        'observation' => 'observation',
        'post_harvest' => 'postHarvestTreatment',
    ];

    // Mapa de slug de ruta → activity_type interno
    private const TYPE_SLUG_MAP = [
        'treatments' => 'phytosanitary',
        'fertilizations' => 'fertilization',
        'irrigations' => 'irrigation',
        'observations' => 'observation',
        'harvests' => 'harvest',
        'cultural-works' => 'cultural',
        'pruning' => 'pruning',
        'post-harvest-treatments' => 'post_harvest',
    ];

    // ─── GET /viticulturist/notebook/{notebook_type} ──────────────────────────
    // Listado por tipo para el cliente móvil. Devuelve formato plano alineado
    // con los DTOs Kotlin. Carga las relaciones de detalle correctas por tipo.

    private const MOBILE_RELATIONS = [
        'phytosanitary' => ['plot', 'phytosanitaryTreatment.product'],
        'fertilization' => ['plot', 'fertilization'],
        'irrigation' => ['plot', 'irrigation'],
        'observation' => ['plot', 'observation'],
        'harvest' => ['plot', 'harvest', 'plotPlanting.grapeVariety'],
        'cultural' => ['plot', 'culturalWork'],
        'pruning' => ['plot', 'culturalWork'],
        'post_harvest' => ['plot', 'postHarvestTreatment.product'],
    ];

    // ─── GET /viticulturist/notebook ──────────────────────────────────────────

    public function index(IndexNotebookRequest $request): JsonResponse
    {
        $query = AgriculturalActivity::forViticulturist($request->user()->id)
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

        return $this->paginated($activities, ActivityResource::collection($activities->items()));
    }

    // ─── GET /viticulturist/notebook/{id} ─────────────────────────────────────

    public function show(ViticulturistApiRequest $request, int|string $id): JsonResponse
    {
        $activity = AgriculturalActivity::forViticulturist($request->user()->id)
            ->findOrFail((int) $id);

        // El móvil consume el detalle con el mismo shape plano que el listado
        // (MobileNotebookResource), por lo que cargamos las mismas relaciones.
        $relations = self::MOBILE_RELATIONS[$activity->activity_type] ?? ['plot'];
        $activity->load($relations);

        return $this->success(new MobileNotebookResource($activity));
    }

    // ─── POST /viticulturist/notebook ─────────────────────────────────────────

    public function store(StoreNotebookRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

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
            'activity_type' => $validated['activity_type'],
            'plot_id' => $validated['plot_id'],
            'activity_date' => $validated['activity_date'],
            'campaign_id' => $validated['campaign_id'],
            'phenological_stage' => $validated['phenological_stage'] ?? null,
            'weather_conditions' => $validated['weather_conditions'] ?? null,
            'temperature' => $validated['temperature'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'viticulturist_id' => $user->id,
        ]);

        $this->createDetails($activity, $validated);
        $this->loadDetails($activity);

        $this->bustDashboardCache($user->id);

        return $this->created(new ActivityResource($activity));
    }

    // ─── PUT /viticulturist/notebook/{id} ─────────────────────────────────────

    public function update(UpdateNotebookRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $activity = AgriculturalActivity::forViticulturist($user->id)->findOrFail((int) $id);

        if ($activity->is_locked) {
            return response()->json(['message' => 'Esta actividad está bloqueada y no puede editarse.'], 422);
        }

        $validated = $request->validated();

        $activity->update(array_intersect_key($validated, array_flip([
            'activity_date', 'phenological_stage', 'weather_conditions', 'temperature', 'notes',
        ])));

        $this->updateDetails($activity, $validated);

        $toLoad = ['plot', 'campaign'];
        $relation = self::DETAIL_RELATIONS[$activity->activity_type] ?? null;
        if ($relation) {
            $toLoad[] = $relation;
        }
        $activity = $activity->fresh($toLoad);

        return $this->success(new ActivityResource($activity));
    }

    // ─── DELETE /viticulturist/notebook/{id} ──────────────────────────────────

    public function destroy(ViticulturistApiRequest $request, int|string $id): JsonResponse
    {
        $user = $request->user();

        $activity = AgriculturalActivity::forViticulturist($user->id)->findOrFail((int) $id);

        if ($activity->is_locked) {
            return response()->json(['message' => 'Esta actividad está bloqueada y no puede eliminarse.'], 422);
        }

        $activity->delete();

        $this->bustDashboardCache($user->id);

        return $this->deleted('Actividad eliminada correctamente.');
    }

    public function indexOfType(IndexTypedNotebookRequest $request, string $notebookType): JsonResponse
    {
        $type = self::TYPE_SLUG_MAP[$notebookType] ?? null;
        if (! $type) {
            return response()->json(['message' => 'Tipo de actividad no válido.'], 404);
        }

        $relations = self::MOBILE_RELATIONS[$type] ?? ['plot'];

        $query = AgriculturalActivity::forViticulturist($request->user()->id)
            ->ofType($type)
            ->with($relations);

        if ($request->filled('plot_id')) {
            $query->forPlot((int) $request->plot_id);
        }

        $activities = $query->orderByDesc('activity_date')
            ->paginate($request->integer('per_page', 15));

        return $this->paginated($activities, MobileNotebookResource::collection($activities->items()));
    }

    // ─── POST /viticulturist/notebook/{notebook_type} ─────────────────────────
    // Creación por tipo: prepareForValidation() en StoreNotebookRequest inyecta
    // activity_type y normaliza aliases de campos del cliente móvil.

    public function storeTyped(StoreNotebookRequest $request, string $notebookType): JsonResponse
    {
        return $this->store($request);
    }

    /** Invalida el cache del dashboard cuando cambian actividades del cuaderno. */
    private function bustDashboardCache(int $userId): void
    {
        Cache::forget("vit_dashboard:{$userId}:".now()->year);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function createDetails(AgriculturalActivity $activity, array $data): void
    {
        match ($data['activity_type']) {
            'phytosanitary' => PhytosanitaryTreatment::create([
                'activity_id' => $activity->id,
                'product_id' => $data['product_id'],
                'pest_id' => $data['pest_id'] ?? null,
                'dose_per_hectare' => $data['dose_per_hectare'] ?? null,
                'total_dose' => $data['total_dose'] ?? null,
                'area_treated' => $data['area_treated'] ?? null,
                'application_method' => $data['application_method'] ?? null,
                'treatment_justification' => $data['treatment_justification'] ?? null,
                'applicator_ropo_number' => $data['applicator_ropo_number'] ?? null,
                'reentry_period_days' => $data['reentry_period_days'] ?? null,
            ]),
            'fertilization' => Fertilization::create([
                'activity_id' => $activity->id,
                'fertilizer_type' => $data['fertilizer_type'] ?? null,
                'fertilizer_name' => $data['fertilizer_name'] ?? null,
                'quantity' => $data['quantity'] ?? null,
                'application_method' => $data['application_method'] ?? null,
                'area_applied' => $data['area_applied'] ?? null,
                'nitrogen_uf' => $data['nitrogen_uf'] ?? null,
                'phosphorus_uf' => $data['phosphorus_uf'] ?? null,
                'potassium_uf' => $data['potassium_uf'] ?? null,
            ]),
            'irrigation' => Irrigation::create([
                'activity_id' => $activity->id,
                'water_volume' => $data['water_volume'] ?? null,
                'water_volume_unit' => $data['water_volume_unit'] ?? null,
                'irrigation_method' => $data['irrigation_method'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'is_fertirrigation' => $data['is_fertirrigation'] ?? false,
                'fertilizer_product' => $data['fertilizer_product'] ?? null,
                'fertilizer_dose_per_ha' => $data['fertilizer_dose_per_ha'] ?? null,
            ]),
            'cultural' => CulturalWork::create([
                'activity_id' => $activity->id,
                'work_type' => $data['work_type'] ?? null,
                'hours_worked' => $data['hours_worked'] ?? null,
                'workers_count' => $data['workers_count'] ?? null,
                'residue_management' => $data['residue_management'] ?? null,
                'description' => $data['description'] ?? null,
            ]),
            'pruning' => CulturalWork::create([
                'activity_id' => $activity->id,
                'work_type' => 'pruning',
                'pruning_type' => $data['pruning_type'] ?? null,
                'productive_buds_per_hectare' => $data['productive_buds_per_hectare'] ?? null,
                'hours_worked' => $data['hours_worked'] ?? null,
                'workers_count' => $data['workers_count'] ?? null,
                'residue_management' => $data['residue_management'] ?? null,
            ]),
            'observation' => Observation::create([
                'activity_id' => $activity->id,
                'pest_id' => $data['pest_id'] ?? null,
                'observation_type' => $data['observation_type'] ?? null,
                'description' => $data['description'] ?? null,
                'severity' => $data['severity'] ?? null,
                'affected_area_percentage' => $data['affected_area_percentage'] ?? null,
                'threshold_exceeded' => $data['threshold_exceeded'] ?? false,
                'follow_up_date' => $data['follow_up_date'] ?? null,
                'action_taken' => $data['action_taken'] ?? null,
            ]),
            'post_harvest' => PostHarvestTreatment::create([
                'activity_id' => $activity->id,
                'application_type' => $data['application_type'],
                'product_id' => $data['product_id'] ?? null,
                'treated_area_ha' => $data['treated_area_ha'] ?? null,
                'dose_per_hectare' => $data['dose_per_hectare'] ?? null,
                'water_volume_liters' => $data['water_volume_liters'] ?? null,
                'reentry_interval_hours' => $data['reentry_interval_hours'] ?? null,
            ]),
            default => null, // phenology, harvest — sin sub-modelo en la API por ahora
        };
    }

    private function updateDetails(AgriculturalActivity $activity, array $data): void
    {
        $allowedFields = match ($activity->activity_type) {
            'phytosanitary' => [
                'product_id', 'pest_id', 'dose_per_hectare', 'total_dose', 'area_treated',
                'application_method', 'treatment_justification', 'applicator_ropo_number', 'reentry_period_days',
            ],
            'fertilization' => [
                'fertilizer_type', 'fertilizer_name', 'quantity', 'application_method',
                'area_applied', 'nitrogen_uf', 'phosphorus_uf', 'potassium_uf',
            ],
            'irrigation' => [
                'water_volume', 'water_volume_unit', 'irrigation_method', 'duration_minutes',
                'is_fertirrigation', 'fertilizer_product', 'fertilizer_dose_per_ha',
            ],
            'cultural', 'pruning' => [
                'work_type', 'hours_worked', 'workers_count', 'residue_management', 'description',
                'pruning_type', 'productive_buds_per_hectare',
            ],
            'observation' => [
                'pest_id', 'observation_type', 'description', 'severity', 'affected_area_percentage',
                'threshold_exceeded', 'follow_up_date', 'action_taken',
            ],
            'post_harvest' => [
                'application_type', 'product_id', 'treated_area_ha', 'dose_per_hectare',
                'water_volume_liters', 'reentry_interval_hours',
            ],
            default => [],
        };

        $detailFields = array_intersect_key($data, array_flip($allowedFields));

        if (empty($detailFields)) {
            return;
        }

        match ($activity->activity_type) {
            'phytosanitary' => $activity->phytosanitaryTreatment?->update($detailFields),
            'fertilization' => $activity->fertilization?->update($detailFields),
            'irrigation' => $activity->irrigation?->update($detailFields),
            'cultural', 'pruning' => $activity->culturalWork?->update($detailFields),
            'observation' => $activity->observation?->update($detailFields),
            'post_harvest' => $activity->postHarvestTreatment?->update($detailFields),
            default => null,
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
