<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineAdditive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineAdditiveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'wine_id'  => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->orderByDesc('application_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage   = min($request->integer('per_page', 20), 100);
        $additives = $query->paginate($perPage);

        return response()->json([
            'data' => $additives->map(fn ($a) => $this->format($a)),
            'meta' => [
                'total'        => $additives->total(),
                'per_page'     => $additives->perPage(),
                'current_page' => $additives->currentPage(),
                'last_page'    => $additives->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($additive)]);
    }

    public function byContainer(Request $request, int $containerId): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        Container::where('user_id', $user->id)->findOrFail($containerId);

        $perPage   = min($request->integer('per_page', 20), 100);
        $additives = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('wine', function ($q) use ($containerId) {
                $q->whereHas('containers', fn ($cq) => $cq->where('containers.id', $containerId));
            })
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->orderByDesc('application_date')
            ->paginate($perPage);

        return response()->json([
            'data' => $additives->map(fn ($a) => $this->format($a)),
            'meta' => [
                'total'        => $additives->total(),
                'per_page'     => $additives->perPage(),
                'current_page' => $additives->currentPage(),
                'last_page'    => $additives->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'                => 'required|integer|exists:wines,id',
            'wine_process_detail_id' => 'nullable|integer|exists:wine_process_details,id',
            'winery_supply_id'       => 'nullable|integer|exists:winery_supplies,id',
            'oenologist_id'          => 'nullable|integer|exists:oenologists,id',
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'additive_name'          => 'required|string|max:255',
            'quantity'               => 'nullable|numeric|min:0',
            'application_date'       => 'required|date',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);

        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $additive = WineAdditive::create([...$validated, 'created_by' => $user->id]);
        $additive->load(['wine', 'supply', 'oenologist', 'unitOfMeasurement']);

        return response()->json([
            'data'    => $this->format($additive),
            'message' => 'Aditivo registrado correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))->findOrFail($id);

        $validated = $request->validate([
            'winery_supply_id'       => 'sometimes|nullable|integer|exists:winery_supplies,id',
            'oenologist_id'          => 'sometimes|nullable|integer|exists:oenologists,id',
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'additive_name'          => 'sometimes|string|max:255',
            'quantity'               => 'sometimes|nullable|numeric|min:0',
            'application_date'       => 'sometimes|date',
            'notes'                  => 'sometimes|nullable|string|max:1000',
        ]);

        $additive->update($validated);
        $additive->load(['wine', 'supply', 'oenologist', 'unitOfMeasurement']);

        return response()->json(['data' => $this->format($additive)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))->findOrFail($id);
        $additive->delete();

        return response()->json(['message' => 'Aditivo eliminado correctamente.']);
    }

    private function format(WineAdditive $a): array
    {
        return [
            'id'               => $a->id,
            'wine_id'          => $a->wine_id,
            'wine_name'        => $a->wine?->name,
            'additive_name'    => $a->additive_name,
            'supply'           => $a->supply ? ['id' => $a->supply->id, 'name' => $a->supply->name] : null,
            'oenologist'       => $a->oenologist ? ['id' => $a->oenologist->id, 'name' => $a->oenologist->full_name] : null,
            'quantity'         => $a->quantity !== null ? (float) $a->quantity : null,
            'unit'             => $a->unitOfMeasurement ? ['id' => $a->unitOfMeasurement->id, 'symbol' => $a->unitOfMeasurement->symbol ?? $a->unitOfMeasurement->name] : null,
            'application_date' => $a->application_date?->toDateString(),
            'notes'            => $a->notes,
            'created_at'       => $a->created_at->toIso8601String(),
        ];
    }
}
