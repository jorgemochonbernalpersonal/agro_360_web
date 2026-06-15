<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\LabelBatch;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabelBatchController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = LabelBatch::forUser($user->id)->with('wine')->orderByDesc('created_at');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }
        if ($request->boolean('with_stock', false)) {
            $query->withStock();
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $batches = $query->paginate($perPage);

        return $this->paginated($batches, $batches->map(fn ($b) => $this->format($b)), [
            'sources' => LabelBatch::SOURCES,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $batch = LabelBatch::forUser($user->id)->with('wine')->findOrFail($id);

        return $this->success($this->format($batch));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'wine_id' => 'nullable|integer|exists:wines,id',
            'name' => 'required|string|max:255',
            'source' => 'nullable|string|in:'.implode(',', array_keys(LabelBatch::SOURCES)),
            'start_number' => 'nullable|integer|min:1',
            'end_number' => 'nullable|integer|min:1',
            'total_quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        $batch = LabelBatch::create([
            ...$validated,
            'user_id' => $user->id,
            'used_quantity' => 0,
            'wasted_quantity' => 0,
        ]);
        $batch->load('wine');

        return $this->created($this->format($batch));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $batch = LabelBatch::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'source' => 'sometimes|nullable|string|in:'.implode(',', array_keys(LabelBatch::SOURCES)),
            'start_number' => 'sometimes|nullable|integer|min:1',
            'end_number' => 'sometimes|nullable|integer|min:1',
            'total_quantity' => 'sometimes|integer|min:1',
            'wasted_quantity' => 'sometimes|integer|min:0',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $batch->update($validated);
        $batch->load('wine');

        return $this->success($this->format($batch));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $batch = LabelBatch::forUser($user->id)->findOrFail($id);
        abort_unless($batch->canDelete(), 422, 'No se puede eliminar un lote con etiquetas ya usadas.');

        $batch->delete();

        return $this->deleted(__('Lote de etiquetas eliminado correctamente.'));
    }

    private function format(LabelBatch $b): array
    {
        return [
            'id' => $b->id,
            'wine_id' => $b->wine_id,
            'wine_name' => $b->wine?->name,
            'name' => $b->name,
            'source' => $b->source,
            'source_label' => $b->source_label,
            'start_number' => $b->start_number,
            'end_number' => $b->end_number,
            'total_quantity' => $b->total_quantity,
            'used_quantity' => $b->used_quantity,
            'wasted_quantity' => $b->wasted_quantity,
            'available_quantity' => $b->available_quantity,
            'usage_percent' => $b->usage_percent,
            'is_empty' => $b->isEmpty(),
            'notes' => $b->notes,
            'created_at' => $b->created_at->toIso8601String(),
        ];
    }
}
