<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Wine;
use App\Models\WineSubproduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineSubproductController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = WineSubproduct::forUser($user->id)
            ->with(['wine', 'unit'])
            ->orderByDesc('subproduct_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $subproducts = $query->paginate($perPage);

        return $this->paginated($subproducts, $subproducts->map(fn ($s) => $this->format($s)), [
            'types' => WineSubproduct::typeOptions(),
            'destinations' => WineSubproduct::destinationOptions(),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subproduct = WineSubproduct::forUser($user->id)->with(['wine', 'unit'])->findOrFail($id);

        return $this->success($this->format($subproduct));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'wine_id' => 'nullable|integer|exists:wines,id',
            'type' => 'required|string|in:'.implode(',', array_keys(WineSubproduct::TYPES)),
            'subproduct_date' => 'required|date',
            'quantity' => 'required|numeric|min:0.001',
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'destination' => 'nullable|string|in:'.implode(',', array_keys(WineSubproduct::DESTINATIONS)),
            'destination_name' => 'nullable|string|max:255',
            'lot_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        $subproduct = WineSubproduct::create([...$validated, 'user_id' => $user->id, 'created_by' => $user->id]);
        $subproduct->load(['wine', 'unit']);

        return $this->created($this->format($subproduct));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subproduct = WineSubproduct::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|string|in:'.implode(',', array_keys(WineSubproduct::TYPES)),
            'subproduct_date' => 'sometimes|date',
            'quantity' => 'sometimes|numeric|min:0.001',
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'destination' => 'sometimes|nullable|string|in:'.implode(',', array_keys(WineSubproduct::DESTINATIONS)),
            'destination_name' => 'sometimes|nullable|string|max:255',
            'lot_number' => 'sometimes|nullable|string|max:100',
            'notes' => 'sometimes|nullable|string|max:1000',
        ]);

        $subproduct->update($validated);
        $subproduct->load(['wine', 'unit']);

        return $this->success($this->format($subproduct));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $subproduct = WineSubproduct::forUser($user->id)->findOrFail($id);
        $subproduct->delete();

        return $this->deleted(__('Subproducto eliminado correctamente.'));
    }

    private function format(WineSubproduct $s): array
    {
        return [
            'id' => $s->id,
            'wine_id' => $s->wine_id,
            'wine_name' => $s->wine?->name,
            'type' => $s->type,
            'type_label' => $s->type_label,
            'subproduct_date' => $s->subproduct_date->toDateString(),
            'quantity' => (float) $s->quantity,
            'unit' => $s->unit ? ['id' => $s->unit->id, 'name' => $s->unit->name] : null,
            'destination' => $s->destination,
            'destination_label' => $s->destination_label,
            'destination_name' => $s->destination_name,
            'lot_number' => $s->lot_number,
            'notes' => $s->notes,
            'created_at' => $s->created_at->toIso8601String(),
        ];
    }
}
