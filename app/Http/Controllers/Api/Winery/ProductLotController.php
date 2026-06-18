<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\IndexProductLotRequest;
use App\Http\Requests\Api\Winery\StoreProductLotRequest;
use App\Http\Requests\Api\Winery\UpdateProductLotRequest;
use App\Http\Resources\Api\ProductLotResource;
use App\Models\ProductLot;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductLotController extends BaseApiController
{
    // ─── GET /winery/product-lots ─────────────────────────────────────────────

    public function index(IndexProductLotRequest $request): JsonResponse
    {
        $user = $request->user();

        $query = ProductLot::forUser($user->id)->with('wine');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }
        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }
        if ($request->has('archived')) {
            $query->where('archived', $request->boolean('archived'));
        } else {
            $query->active();
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $lots = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->paginated($lots, ProductLotResource::collection($lots));
    }

    // ─── GET /winery/product-lots/{id} ───────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $lot = ProductLot::forUser($user->id)->with('wine')->findOrFail($id);

        return $this->success(new ProductLotResource($lot));
    }

    // ─── POST /winery/product-lots ────────────────────────────────────────────

    public function store(StoreProductLotRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        $lot = ProductLot::create(array_merge($validated, [
            'user_id' => $user->id,
            'initial_quantity' => $validated['quantity'],
            'available_quantity' => $validated['quantity'],
            'reserved_quantity' => 0,
            'sold_quantity' => 0,
        ]));

        $lot->load('wine');

        return $this->created(new ProductLotResource($lot));
    }

    // ─── PUT /winery/product-lots/{id} ───────────────────────────────────────

    public function update(UpdateProductLotRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $lot = ProductLot::forUser($user->id)->findOrFail($id);

        $validated = $request->validated();

        $lot->update($validated);
        $lot->load('wine');

        return $this->success(new ProductLotResource($lot));
    }

    // ─── DELETE /winery/product-lots/{id} ────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $lot = ProductLot::forUser($user->id)->findOrFail($id);
        $lot->update(['archived' => true]);

        return $this->deleted(__('Lote archivado correctamente.'));
    }
}
