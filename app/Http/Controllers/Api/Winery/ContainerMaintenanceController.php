<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\CompleteMaintenanceRequest;
use App\Http\Requests\Api\Winery\IndexContainerMaintenanceRequest;
use App\Http\Requests\Api\Winery\StoreContainerMaintenanceRequest;
use App\Http\Requests\Api\Winery\UpdateContainerMaintenanceRequest;
use App\Http\Requests\Api\Winery\WineryApiRequest;
use App\Http\Resources\Api\ContainerMaintenanceResource;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\ContainerMaintenanceSupply;
use App\Models\ContainerMaintenanceWaste;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ContainerMaintenanceController extends BaseApiController
{
    // ─── GET /winery/maintenances ─────────────────────────────────────────────

    public function index(IndexContainerMaintenanceRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->with(['container']);

        if ($request->filled('container_id')) {
            $query->where('container_id', $request->integer('container_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('maintenance_type', $request->type);
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $maintenances = $query->orderByDesc('scheduled_date')->paginate($perPage);

        // Status counts
        $counts = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->selectRaw("
            SUM(CASE WHEN status = 'scheduled'   THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed'   THEN 1 ELSE 0 END) as completed
        ")->first();

        return $this->paginated($maintenances, ContainerMaintenanceResource::collection($maintenances), [
            'scheduled' => (int) $counts->scheduled,
            'in_progress' => (int) $counts->in_progress,
            'completed' => (int) $counts->completed,
            'types' => ContainerMaintenance::typeOptions(),
            'statuses' => ContainerMaintenance::statusOptions(),
        ]);
    }

    // ─── GET /winery/maintenances/{id} ───────────────────────────────────────

    public function show(WineryApiRequest $request, int $id): JsonResponse
    {
        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $request->user()->id)
        )->with(['container', 'supplies', 'wastes'])->findOrFail($id);

        return $this->success(new ContainerMaintenanceResource($maintenance));
    }

    // ─── POST /winery/maintenances ────────────────────────────────────────────

    public function store(StoreContainerMaintenanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $suppliesData = $validated['supplies'] ?? [];
        $wastesData = $validated['wastes'] ?? [];
        unset($validated['supplies'], $validated['wastes']);

        $maintenance = DB::transaction(function () use ($validated, $suppliesData, $wastesData) {
            $maintenance = ContainerMaintenance::create([
                ...$validated,
                'status' => $validated['status'] ?? 'scheduled',
            ]);

            foreach ($suppliesData as $s) {
                ContainerMaintenanceSupply::create([
                    'container_maintenance_id' => $maintenance->id,
                    'winery_supply_id' => $s['winery_supply_id'] ?? null,
                    'supply_name' => $s['supply_name'] ?? null,
                    'quantity_used' => $s['quantity_used'] ?? null,
                    'unit_of_measurement_id' => $s['unit_of_measurement_id'] ?? null,
                    'cost' => $s['cost'] ?? null,
                    'notes' => $s['notes'] ?? null,
                ]);
            }

            foreach ($wastesData as $w) {
                ContainerMaintenanceWaste::create([
                    'container_maintenance_id' => $maintenance->id,
                    'container_waste_type_id' => $w['container_waste_type_id'] ?? null,
                    'custom_waste_type' => $w['custom_waste_type'] ?? null,
                    'waste_date' => $w['waste_date'] ?? null,
                    'quantity' => $w['quantity'] ?? null,
                    'unit_of_measurement_id' => $w['unit_of_measurement_id'] ?? null,
                    'disposal_method' => $w['disposal_method'] ?? null,
                    'cost' => $w['cost'] ?? null,
                    'notes' => $w['notes'] ?? null,
                ]);
            }

            return $maintenance;
        });

        $maintenance->load(['container', 'supplies', 'wastes']);

        return $this->created(new ContainerMaintenanceResource($maintenance));
    }

    // ─── PUT /winery/maintenances/{id} ───────────────────────────────────────

    public function update(UpdateContainerMaintenanceRequest $request, int $id): JsonResponse
    {
        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $request->user()->id)
        )->findOrFail($id);

        $maintenance->update($request->validated());
        $maintenance->load(['container', 'supplies', 'wastes']);

        return $this->success(new ContainerMaintenanceResource($maintenance));
    }

    // ─── DELETE /winery/maintenances/{id} ────────────────────────────────────

    public function destroy(WineryApiRequest $request, int $id): JsonResponse
    {
        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $request->user()->id)
        )->findOrFail($id);

        DB::transaction(function () use ($maintenance) {
            $maintenance->supplies()->delete();
            $maintenance->wastes()->delete();
            $maintenance->delete();
        });

        return $this->deleted(__('Mantenimiento eliminado correctamente.'));
    }

    // ─── POST /winery/maintenances/{id}/complete ─────────────────────────────

    public function complete(CompleteMaintenanceRequest $request, int $id): JsonResponse
    {
        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $request->user()->id)
        )->findOrFail($id);

        $validated = $request->validated();

        $maintenance->update([
            ...$validated,
            'status' => 'completed',
            'performed_date' => $validated['performed_date'] ?? now()->toDateString(),
        ]);

        $maintenance->load(['container', 'supplies', 'wastes']);

        return $this->success(new ContainerMaintenanceResource($maintenance));
    }

    // ─── GET /winery/containers/{id}/maintenances ─────────────────────────────

    public function byContainer(WineryApiRequest $request, int $containerId): JsonResponse
    {
        Container::where('user_id', $request->user()->id)->findOrFail($containerId);

        $perPage = $this->resolvePerPage($request, 20, 100);
        $maintenances = ContainerMaintenance::where('container_id', $containerId)
            ->with(['supplies', 'wastes'])
            ->orderByDesc('scheduled_date')
            ->paginate($perPage);

        return $this->paginated($maintenances, ContainerMaintenanceResource::collection($maintenances));
    }
}
