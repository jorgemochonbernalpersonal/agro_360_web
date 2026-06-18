<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreWineAdditiveRequest;
use App\Http\Requests\Api\Winery\UpdateWineAdditiveRequest;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\Wine;
use App\Models\WineAdditive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineAdditiveController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'wine_id' => 'nullable|integer',
        ]);

        $query = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->orderByDesc('application_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $additives = $query->paginate($perPage);

        return $this->paginated($additives, $additives->map(fn ($a) => $this->format($a)));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->findOrFail($id);

        return $this->success($this->format($additive));
    }

    public function byContainer(Request $request, int $containerId): JsonResponse
    {
        $user = $request->user();

        Container::where('user_id', $user->id)->findOrFail($containerId);

        $perPage = $this->resolvePerPage($request, 20, 100);
        $additives = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->whereHas('wine', function ($q) use ($containerId) {
                $q->whereHas('containers', fn ($cq) => $cq->where('containers.id', $containerId));
            })
            ->with(['wine', 'supply', 'oenologist', 'unitOfMeasurement'])
            ->orderByDesc('application_date')
            ->paginate($perPage);

        return $this->paginated($additives, $additives->map(fn ($a) => $this->format($a)));
    }

    public function store(StoreWineAdditiveRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);

        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $additive = WineAdditive::create([...$validated, 'created_by' => $user->id]);
        $additive->load(['wine', 'supply', 'oenologist', 'unitOfMeasurement']);

        return $this->created($this->format($additive));
    }

    public function update(UpdateWineAdditiveRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))->findOrFail($id);

        $validated = $request->validated();

        $additive->update($validated);
        $additive->load(['wine', 'supply', 'oenologist', 'unitOfMeasurement']);

        return $this->success($this->format($additive));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $additive = WineAdditive::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))->findOrFail($id);
        $additive->delete();

        return $this->deleted(__('Aditivo eliminado correctamente.'));
    }

    private function format(WineAdditive $a): array
    {
        return [
            'id' => $a->id,
            'wine_id' => $a->wine_id,
            'wine_name' => $a->wine?->name,
            'additive_name' => $a->additive_name,
            'supply' => $a->supply ? ['id' => $a->supply->id, 'name' => $a->supply->name] : null,
            'oenologist' => $a->oenologist ? ['id' => $a->oenologist->id, 'name' => $a->oenologist->full_name] : null,
            'quantity' => (float) $a->quantity,
            'unit' => $a->unitOfMeasurement ? ['id' => $a->unitOfMeasurement->id, 'symbol' => $a->unitOfMeasurement->symbol ?? $a->unitOfMeasurement->name] : null,
            'application_date' => $a->application_date->toDateString(),
            'notes' => $a->notes,
            'created_at' => $a->created_at->toIso8601String(),
        ];
    }
}
