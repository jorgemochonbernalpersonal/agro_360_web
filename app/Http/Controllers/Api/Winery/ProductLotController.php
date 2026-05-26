<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ProductLotResource;
use App\Models\ProductLot;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductLotController extends Controller
{
    // ─── GET /winery/product-lots ─────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'wine_id'  => 'nullable|integer',
            'unit'     => 'nullable|string|in:litros,botellas,cajas',
            'archived' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

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
        $lots    = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json([
            'data' => ProductLotResource::collection($lots),
            'meta' => [
                'total'        => $lots->total(),
                'per_page'     => $lots->perPage(),
                'current_page' => $lots->currentPage(),
                'last_page'    => $lots->lastPage(),
            ],
        ]);
    }

    // ─── GET /winery/product-lots/{id} ───────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $lot = ProductLot::forUser($user->id)->with('wine')->findOrFail($id);

        return response()->json(['data' => new ProductLotResource($lot)]);
    }

    // ─── POST /winery/product-lots ────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'          => 'nullable|integer|exists:wines,id',
            'name'             => 'required|string|max:255',
            'vintage'          => 'nullable|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'        => 'nullable|string|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type'       => 'nullable|string|max:100',
            'agingtime'        => 'nullable|integer|min:0',
            'alcohol'          => 'nullable|numeric|min:0|max:25',
            'quantity'         => 'required|numeric|min:0',
            'unit'             => 'required|string|in:litros,botellas,cajas',
            'price_per_unit'   => 'nullable|numeric|min:0',
            'cost_price'       => 'nullable|numeric|min:0',
            // Formato
            'bottle_format'    => 'nullable|string|max:50',
            'units_per_case'   => 'nullable|integer|min:1',
            'ean'              => 'nullable|string|max:50',
            'sku'              => 'nullable|string|max:100',
            // Analíticos
            'residual_sugar'   => 'nullable|numeric|min:0',
            'total_acidity'    => 'nullable|numeric|min:0',
            'volatile_acidity' => 'nullable|numeric|min:0',
            'ph'               => 'nullable|numeric|between:2,5',
            // Winemaking
            'winemaker'           => 'nullable|string|max:255',
            'fermentation_vessel' => 'nullable|string|max:255',
            'oak_type'            => 'nullable|string|max:100',
            'oak_months'          => 'nullable|integer|min:0',
            'harvest_method'      => 'nullable|string|max:100',
            // Viñedo
            'vine_age'  => 'nullable|integer|min:0',
            'altitude'  => 'nullable|integer|min:0',
            'soil_type' => 'nullable|string|max:100',
            // Certificaciones
            'is_vegan'     => 'nullable|boolean',
            'is_biodynamic'=> 'nullable|boolean',
            'sulfites'     => 'nullable|boolean',
            'ecological'   => 'nullable|boolean',
            // Marketing
            'description'                 => 'nullable|string|max:5000',
            'pairing'                     => 'nullable|string|max:2000',
            'tasting_notes'               => 'nullable|string|max:2000',
            'consumption_recommendation'  => 'nullable|string|max:1000',
            'recommended_temperature_min' => 'nullable|numeric|between:-5,30',
            'recommended_temperature_max' => 'nullable|numeric|between:-5,30',
            'awards_notes'                => 'nullable|string|max:2000',
            'production_quantity'         => 'nullable|integer|min:0',
            'bottling_date'               => 'nullable|date',
            'release_date'                => 'nullable|date',
            'notes'                       => 'nullable|string|max:2000',
        ]);

        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        $lot = ProductLot::create(array_merge($validated, [
            'user_id'           => $user->id,
            'initial_quantity'  => $validated['quantity'],
            'available_quantity'=> $validated['quantity'],
            'reserved_quantity' => 0,
            'sold_quantity'     => 0,
        ]));

        $lot->load('wine');

        return response()->json([
            'data'    => new ProductLotResource($lot),
            'message' => __('Lote de producto creado correctamente.'),
        ], 201);
    }

    // ─── PUT /winery/product-lots/{id} ───────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $lot = ProductLot::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'vintage'          => 'sometimes|nullable|integer|min:1900|max:' . (now()->year + 2),
            'wine_type'        => 'sometimes|nullable|string|in:tinto,blanco,rosado,espumoso,otro',
            'aging_type'       => 'sometimes|nullable|string|max:100',
            'agingtime'        => 'sometimes|nullable|integer|min:0',
            'alcohol'          => 'sometimes|nullable|numeric|min:0|max:25',
            'price_per_unit'   => 'sometimes|nullable|numeric|min:0',
            'cost_price'       => 'sometimes|nullable|numeric|min:0',
            'bottle_format'    => 'sometimes|nullable|string|max:50',
            'units_per_case'   => 'sometimes|nullable|integer|min:1',
            'ean'              => 'sometimes|nullable|string|max:50',
            'sku'              => 'sometimes|nullable|string|max:100',
            'residual_sugar'   => 'sometimes|nullable|numeric|min:0',
            'total_acidity'    => 'sometimes|nullable|numeric|min:0',
            'volatile_acidity' => 'sometimes|nullable|numeric|min:0',
            'ph'               => 'sometimes|nullable|numeric|between:2,5',
            'winemaker'           => 'sometimes|nullable|string|max:255',
            'fermentation_vessel' => 'sometimes|nullable|string|max:255',
            'oak_type'            => 'sometimes|nullable|string|max:100',
            'oak_months'          => 'sometimes|nullable|integer|min:0',
            'harvest_method'      => 'sometimes|nullable|string|max:100',
            'vine_age'            => 'sometimes|nullable|integer|min:0',
            'altitude'            => 'sometimes|nullable|integer|min:0',
            'soil_type'           => 'sometimes|nullable|string|max:100',
            'is_vegan'            => 'sometimes|nullable|boolean',
            'is_biodynamic'       => 'sometimes|nullable|boolean',
            'sulfites'            => 'sometimes|nullable|boolean',
            'ecological'          => 'sometimes|nullable|boolean',
            'description'                 => 'sometimes|nullable|string|max:5000',
            'pairing'                     => 'sometimes|nullable|string|max:2000',
            'tasting_notes'               => 'sometimes|nullable|string|max:2000',
            'consumption_recommendation'  => 'sometimes|nullable|string|max:1000',
            'recommended_temperature_min' => 'sometimes|nullable|numeric|between:-5,30',
            'recommended_temperature_max' => 'sometimes|nullable|numeric|between:-5,30',
            'awards_notes'                => 'sometimes|nullable|string|max:2000',
            'production_quantity'         => 'sometimes|nullable|integer|min:0',
            'bottling_date'               => 'sometimes|nullable|date',
            'release_date'                => 'sometimes|nullable|date',
            'archived'                    => 'sometimes|boolean',
            'notes'                       => 'sometimes|nullable|string|max:2000',
        ]);

        $lot->update($validated);
        $lot->load('wine');

        return response()->json(['data' => new ProductLotResource($lot)]);
    }

    // ─── DELETE /winery/product-lots/{id} ────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $lot = ProductLot::forUser($user->id)->findOrFail($id);
        $lot->update(['archived' => true]);

        return response()->json(['message' => __('Lote archivado correctamente.')]);
    }
}
