<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\LabelBatch;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineLabeling;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabelingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = WineLabeling::forUser($user->id)
            ->with(['wine', 'bottling', 'labelBatch'])
            ->orderByDesc('labeling_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage   = $this->resolvePerPage($request, 20, 100);
        $labelings = $query->paginate($perPage);

        return response()->json([
            'data' => $labelings->map(fn ($l) => $this->format($l)),
            'meta' => [
                'total'        => $labelings->total(),
                'per_page'     => $labelings->perPage(),
                'current_page' => $labelings->currentPage(),
                'last_page'    => $labelings->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $labeling = WineLabeling::forUser($user->id)->with(['wine', 'bottling', 'labelBatch'])->findOrFail($id);

        return response()->json(['data' => $this->format($labeling)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'          => 'required|integer|exists:wines,id',
            'wine_bottling_id' => 'nullable|integer|exists:wine_bottlings,id',
            'label_batch_id'   => 'nullable|integer|exists:label_batches,id',
            'labeling_date'    => 'required|date',
            'quantity_labeled' => 'required|integer|min:1',
            'from_number'      => 'nullable|integer|min:1',
            'to_number'        => 'nullable|integer|min:1',
            'notes'            => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);

        if (isset($validated['wine_bottling_id'])) {
            WineBottling::forUser($user->id)->findOrFail($validated['wine_bottling_id']);
        }

        $batch = null;
        if (isset($validated['label_batch_id'])) {
            $batch = LabelBatch::forUser($user->id)->findOrFail($validated['label_batch_id']);
            abort_if(
                $batch->available_quantity < $validated['quantity_labeled'],
                422,
                'El lote no tiene suficientes etiquetas disponibles.'
            );
        }

        $labeling = DB::transaction(function () use ($validated, $batch, $user) {
            $labeling = WineLabeling::create([
                ...$validated,
                'user_id'    => $user->id,
                'created_by' => $user->id,
            ]);

            if ($batch) {
                $batch->increment('used_quantity', $validated['quantity_labeled']);
            }

            return $labeling;
        });

        $labeling->load(['wine', 'bottling', 'labelBatch']);

        return response()->json([
            'data'    => $this->format($labeling),
            'message' => 'Etiquetado registrado correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $labeling = WineLabeling::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'labeling_date'    => 'sometimes|date',
            'quantity_labeled' => 'sometimes|integer|min:1',
            'from_number'      => 'sometimes|nullable|integer|min:1',
            'to_number'        => 'sometimes|nullable|integer|min:1',
            'notes'            => 'sometimes|nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($labeling, $validated) {
            // Recalculate label batch usage when quantity changes
            if (isset($validated['quantity_labeled']) && $labeling->label_batch_id) {
                $diff = $validated['quantity_labeled'] - $labeling->quantity_labeled;
                if ($diff !== 0) {
                    $batch = LabelBatch::find($labeling->label_batch_id);
                    if ($batch) {
                        abort_if(
                            $diff > 0 && $batch->available_quantity < $diff,
                            422,
                            'El lote no tiene suficientes etiquetas disponibles.'
                        );
                        $batch->increment('used_quantity', $diff);
                    }
                }
            }
            $labeling->update($validated);
        });

        $labeling->load(['wine', 'bottling', 'labelBatch']);

        return response()->json(['data' => $this->format($labeling)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $labeling = WineLabeling::forUser($user->id)->findOrFail($id);

        DB::transaction(function () use ($labeling) {
            // Devolver etiquetas al lote si existe
            if ($labeling->label_batch_id && $labeling->quantity_labeled) {
                LabelBatch::where('id', $labeling->label_batch_id)
                    ->decrement('used_quantity', $labeling->quantity_labeled);
            }
            $labeling->delete();
        });

        return response()->json(['message' => 'Etiquetado eliminado correctamente.']);
    }

    private function format(WineLabeling $l): array
    {
        return [
            'id'               => $l->id,
            'wine_id'          => $l->wine_id,
            'wine_name'        => $l->wine?->name,
            'bottling_id'      => $l->wine_bottling_id,
            'label_batch'      => $l->labelBatch ? [
                'id'                 => $l->labelBatch->id,
                'name'               => $l->labelBatch->name,
                'available_quantity' => $l->labelBatch->available_quantity,
            ] : null,
            'labeling_date'    => $l->labeling_date?->toDateString(),
            'quantity_labeled' => $l->quantity_labeled,
            'label_range'      => $l->label_range,
            'from_number'      => $l->from_number,
            'to_number'        => $l->to_number,
            'notes'            => $l->notes,
            'created_at'       => $l->created_at->toIso8601String(),
        ];
    }
}
