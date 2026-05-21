<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContainerMaintenanceResource;
use App\Models\Container;
use App\Models\ContainerMaintenance;
use App\Models\ContainerMaintenanceSupply;
use App\Models\ContainerMaintenanceWaste;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContainerMaintenanceController extends Controller
{
    // ─── GET /winery/maintenances ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'container_id' => 'nullable|integer',
            'status'       => 'nullable|string|in:' . implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'type'         => 'nullable|string|in:' . implode(',', array_keys(ContainerMaintenance::TYPES)),
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

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

        $perPage      = $this->resolvePerPage($request, 20, 100);
        $maintenances = $query->orderByDesc('scheduled_date')->paginate($perPage);

        // Status counts
        $counts = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->selectRaw("
            SUM(CASE WHEN status = 'scheduled'   THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'completed'   THEN 1 ELSE 0 END) as completed
        ")->first();

        return response()->json([
            'data' => ContainerMaintenanceResource::collection($maintenances),
            'meta' => [
                'total'        => $maintenances->total(),
                'per_page'     => $maintenances->perPage(),
                'current_page' => $maintenances->currentPage(),
                'last_page'    => $maintenances->lastPage(),
                'scheduled'    => (int) $counts->scheduled,
                'in_progress'  => (int) $counts->in_progress,
                'completed'    => (int) $counts->completed,
                'types'        => ContainerMaintenance::TYPES,
                'statuses'     => ContainerMaintenance::STATUSES,
            ],
        ]);
    }

    // ─── GET /winery/maintenances/{id} ───────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->with(['container', 'supplies', 'wastes'])->findOrFail($id);

        return response()->json(['data' => new ContainerMaintenanceResource($maintenance)]);
    }

    // ─── POST /winery/maintenances ────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'container_id'          => 'required|integer|exists:containers,id',
            'maintenance_type'      => 'required|string|in:' . implode(',', array_keys(ContainerMaintenance::TYPES)),
            'maintenance_name'      => 'required|string|max:255',
            'scheduled_date'        => 'nullable|date',
            'performed_date'        => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'status'                => 'nullable|string|in:' . implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'cost'                  => 'nullable|numeric|min:0',
            'performed_by'          => 'nullable|string|max:255',
            'notes'                 => 'nullable|string|max:2000',
            // Insumos usados
            'supplies'                           => 'nullable|array|max:20',
            'supplies.*.winery_supply_id'        => 'nullable|integer|exists:winery_supplies,id',
            'supplies.*.supply_name'             => 'nullable|string|max:255',
            'supplies.*.quantity_used'           => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id'  => 'nullable|integer|exists:unit_of_measurements,id',
            'supplies.*.cost'                    => 'nullable|numeric|min:0',
            'supplies.*.notes'                   => 'nullable|string|max:500',
            // Residuos generados
            'wastes'                              => 'nullable|array|max:10',
            'wastes.*.container_waste_type_id'   => 'nullable|integer|exists:container_waste_types,id',
            'wastes.*.custom_waste_type'         => 'nullable|string|max:100',
            'wastes.*.waste_date'                => 'nullable|date',
            'wastes.*.quantity'                  => 'nullable|numeric|min:0',
            'wastes.*.unit_of_measurement_id'    => 'nullable|integer|exists:unit_of_measurements,id',
            'wastes.*.disposal_method'           => 'nullable|string|max:255',
            'wastes.*.cost'                      => 'nullable|numeric|min:0',
            'wastes.*.notes'                     => 'nullable|string|max:500',
        ]);

        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $suppliesData = $validated['supplies'] ?? [];
        $wastesData   = $validated['wastes'] ?? [];
        unset($validated['supplies'], $validated['wastes']);

        $maintenance = DB::transaction(function () use ($validated, $suppliesData, $wastesData) {
            $maintenance = ContainerMaintenance::create([
                ...$validated,
                'status' => $validated['status'] ?? 'scheduled',
            ]);

            foreach ($suppliesData as $s) {
                ContainerMaintenanceSupply::create([
                    'container_maintenance_id' => $maintenance->id,
                    'winery_supply_id'         => $s['winery_supply_id'] ?? null,
                    'supply_name'              => $s['supply_name'] ?? null,
                    'quantity_used'            => $s['quantity_used'] ?? null,
                    'unit_of_measurement_id'   => $s['unit_of_measurement_id'] ?? null,
                    'cost'                     => $s['cost'] ?? null,
                    'notes'                    => $s['notes'] ?? null,
                ]);
            }

            foreach ($wastesData as $w) {
                ContainerMaintenanceWaste::create([
                    'container_maintenance_id' => $maintenance->id,
                    'container_waste_type_id'  => $w['container_waste_type_id'] ?? null,
                    'custom_waste_type'        => $w['custom_waste_type'] ?? null,
                    'waste_date'               => $w['waste_date'] ?? null,
                    'quantity'                 => $w['quantity'] ?? null,
                    'unit_of_measurement_id'   => $w['unit_of_measurement_id'] ?? null,
                    'disposal_method'          => $w['disposal_method'] ?? null,
                    'cost'                     => $w['cost'] ?? null,
                    'notes'                    => $w['notes'] ?? null,
                ]);
            }

            return $maintenance;
        });

        $maintenance->load(['container', 'supplies', 'wastes']);

        return response()->json([
            'data'    => new ContainerMaintenanceResource($maintenance),
            'message' => 'Mantenimiento registrado correctamente.',
        ], 201);
    }

    // ─── PUT /winery/maintenances/{id} ───────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'maintenance_type'      => 'sometimes|string|in:' . implode(',', array_keys(ContainerMaintenance::TYPES)),
            'maintenance_name'      => 'sometimes|string|max:255',
            'scheduled_date'        => 'sometimes|nullable|date',
            'performed_date'        => 'sometimes|nullable|date',
            'next_maintenance_date' => 'sometimes|nullable|date',
            'status'                => 'sometimes|string|in:' . implode(',', array_keys(ContainerMaintenance::STATUSES)),
            'cost'                  => 'sometimes|nullable|numeric|min:0',
            'performed_by'          => 'sometimes|nullable|string|max:255',
            'notes'                 => 'sometimes|nullable|string|max:2000',
        ]);

        $maintenance->update($validated);
        $maintenance->load(['container', 'supplies', 'wastes']);

        return response()->json(['data' => new ContainerMaintenanceResource($maintenance)]);
    }

    // ─── DELETE /winery/maintenances/{id} ────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        DB::transaction(function () use ($maintenance) {
            $maintenance->supplies()->delete();
            $maintenance->wastes()->delete();
            $maintenance->delete();
        });

        return response()->json(['message' => 'Mantenimiento eliminado correctamente.']);
    }

    // ─── POST /winery/maintenances/{id}/complete ─────────────────────────────

    public function complete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $maintenance = ContainerMaintenance::whereHas(
            'container', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        $validated = $request->validate([
            'performed_date'        => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
            'cost'                  => 'nullable|numeric|min:0',
            'performed_by'          => 'nullable|string|max:255',
            'notes'                 => 'nullable|string|max:2000',
        ]);

        $maintenance->update([
            ...$validated,
            'status'         => 'completed',
            'performed_date' => $validated['performed_date'] ?? now()->toDateString(),
        ]);

        $maintenance->load(['container', 'supplies', 'wastes']);

        return response()->json([
            'data'    => new ContainerMaintenanceResource($maintenance),
            'message' => 'Mantenimiento completado correctamente.',
        ]);
    }

    // ─── GET /winery/containers/{id}/maintenances ─────────────────────────────

    public function byContainer(Request $request, int $containerId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        Container::where('user_id', $user->id)->findOrFail($containerId);

        $perPage      = $this->resolvePerPage($request, 20, 100);
        $maintenances = ContainerMaintenance::where('container_id', $containerId)
            ->with(['supplies', 'wastes'])
            ->orderByDesc('scheduled_date')
            ->paginate($perPage);

        return response()->json([
            'data' => ContainerMaintenanceResource::collection($maintenances),
            'meta' => [
                'total'        => $maintenances->total(),
                'per_page'     => $maintenances->perPage(),
                'current_page' => $maintenances->currentPage(),
                'last_page'    => $maintenances->lastPage(),
            ],
        ]);
    }
}
