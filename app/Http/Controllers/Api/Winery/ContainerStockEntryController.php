<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\StockEntryResource;
use App\Models\Container;
use App\Models\Wine;
use App\Models\WineContainerStockEntry;
use App\Services\WineContainerStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContainerStockEntryController extends BaseApiController
{
    // ─── GET /winery/container-stock-entries ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $perPage = $this->resolvePerPage($request, 20, 50);

        $entries = WineContainerStockEntry::whereHas('wine', fn ($q) => $q->where('user_id', $user->id))
            ->with(['wine', 'container'])
            ->latest('entry_date')
            ->paginate($perPage);

        return $this->paginated($entries, StockEntryResource::collection($entries));
    }

    // ─── POST /winery/container-stock-entries ─────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id' => 'required|integer|exists:wines,id',
            'container_id' => 'required|integer|exists:containers,id',
            'quantity_liters' => 'required|numeric|min:0.001',
            'entry_date' => 'required|date',
            'source' => 'nullable|string|in:initial_stock,adjustment,correction',
            'notes' => 'nullable|string|max:1000',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        Container::where('user_id', $user->id)->findOrFail($validated['container_id']);

        $entry = DB::transaction(function () use ($validated, $user) {
            $entry = WineContainerStockEntry::create([
                ...$validated,
                'source' => $validated['source'] ?? 'initial_stock',
                'created_by' => $user->id,
            ]);
            app(WineContainerStockService::class)->recordStockEntry($entry);

            return $entry;
        });

        $entry->load(['wine', 'container']);

        return $this->created(new StockEntryResource($entry));
    }

    // ─── DELETE /winery/container-stock-entries/{id} ──────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $entry = WineContainerStockEntry::whereHas(
            'wine', fn ($q) => $q->where('user_id', $user->id)
        )->findOrFail($id);

        DB::transaction(function () use ($entry) {
            app(WineContainerStockService::class)->revertStockEntry($entry);
            $entry->delete();
        });

        return $this->deleted(__('Entrada de stock eliminada correctamente.'));
    }
}
