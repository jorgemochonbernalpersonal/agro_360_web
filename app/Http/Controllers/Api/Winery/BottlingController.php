<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BottlingResource;
use App\Models\Container;
use App\Models\Oenologist;
use App\Models\ProductLot;
use App\Models\Wine;
use App\Models\WineBottling;
use App\Models\WineBottlingSupply;
use App\Services\WineContainerStockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BottlingController extends Controller
{
    // ─── GET /winery/bottlings ────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'wine_id'  => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = WineBottling::forUser($user->id)
            ->with(['wine', 'container', 'productLot', 'oenologist'])
            ->orderByDesc('bottling_date');

        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage    = $this->resolvePerPage($request, 20, 100);
        $bottlings  = $query->paginate($perPage);

        return response()->json([
            'data' => BottlingResource::collection($bottlings),
            'meta' => [
                'total'        => $bottlings->total(),
                'per_page'     => $bottlings->perPage(),
                'current_page' => $bottlings->currentPage(),
                'last_page'    => $bottlings->lastPage(),
                'bottle_formats' => WineBottling::bottleFormatOptions(),
            ],
        ]);
    }

    // ─── GET /winery/bottlings/{id} ───────────────────────────────────────────

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $bottling = WineBottling::forUser($user->id)
            ->with(['wine', 'container', 'productLot', 'oenologist', 'supplies'])
            ->findOrFail($id);

        return response()->json(['data' => new BottlingResource($bottling)]);
    }

    // ─── POST /winery/bottlings ───────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'wine_id'                 => 'required|integer|exists:wines,id',
            'container_id'            => 'nullable|integer|exists:containers,id',
            'wine_process_detail_id'  => 'nullable|integer|exists:wine_process_details,id',
            'product_lot_id'          => 'nullable|integer|exists:wine_lots,id',
            'oenologist_id'           => 'nullable|integer|exists:oenologists,id',
            'bottling_date'           => 'required|date',
            'bottle_format'           => 'required|string|in:' . implode(',', array_keys(WineBottling::BOTTLE_FORMATS)),
            'quantity_bottles'        => 'required|integer|min:1',
            'quantity_liters'         => 'required|numeric|min:0',
            'lot_number'              => 'nullable|string|max:100',
            'notes'                   => 'nullable|string|max:2000',
            // Insumos usados en el embotellado
            'supplies'                        => 'nullable|array|max:20',
            'supplies.*.winery_supply_id'     => 'nullable|integer|exists:winery_supplies,id',
            'supplies.*.supply_name'          => 'required_without:supplies.*.winery_supply_id|string|max:255',
            'supplies.*.quantity'             => 'nullable|numeric|min:0',
            'supplies.*.unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'supplies.*.notes'                => 'nullable|string|max:500',
        ]);

        Wine::forUser($user->id)->findOrFail($validated['wine_id']);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }
        if (isset($validated['product_lot_id'])) {
            ProductLot::forUser($user->id)->findOrFail($validated['product_lot_id']);
        }
        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $suppliesData = $validated['supplies'] ?? [];
        unset($validated['supplies']);

        $bottling = DB::transaction(function () use ($validated, $suppliesData, $user) {
            $bottling = WineBottling::create([
                ...$validated,
                'user_id'    => $user->id,
                'created_by' => $user->id,
            ]);

            foreach ($suppliesData as $supply) {
                WineBottlingSupply::create([
                    'wine_bottling_id'       => $bottling->id,
                    'winery_supply_id'       => $supply['winery_supply_id'] ?? null,
                    'supply_name'            => $supply['supply_name'] ?? null,
                    'quantity'               => $supply['quantity'] ?? null,
                    'unit_of_measurement_id' => $supply['unit_of_measurement_id'] ?? null,
                    'notes'                  => $supply['notes'] ?? null,
                ]);
            }

            app(WineContainerStockService::class)->recordBottling($bottling);

            return $bottling;
        });

        $bottling->load(['wine', 'container', 'productLot', 'oenologist', 'supplies']);

        return response()->json([
            'data'    => new BottlingResource($bottling),
            'message' => __('Embotellado registrado correctamente.'),
        ], 201);
    }

    // ─── PUT /winery/bottlings/{id} ───────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $bottling = WineBottling::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'container_id'   => 'sometimes|nullable|integer|exists:containers,id',
            'product_lot_id' => 'sometimes|nullable|integer|exists:wine_lots,id',
            'oenologist_id'  => 'sometimes|nullable|integer|exists:oenologists,id',
            'bottling_date'  => 'sometimes|date',
            'bottle_format'  => 'sometimes|string|in:' . implode(',', array_keys(WineBottling::BOTTLE_FORMATS)),
            'quantity_bottles' => 'sometimes|integer|min:1',
            'quantity_liters'  => 'sometimes|numeric|min:0',
            'lot_number'       => 'sometimes|nullable|string|max:100',
            'notes'            => 'sometimes|nullable|string|max:2000',
        ]);

        if (isset($validated['container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['container_id']);
        }
        if (isset($validated['product_lot_id'])) {
            ProductLot::forUser($user->id)->findOrFail($validated['product_lot_id']);
        }
        if (isset($validated['oenologist_id'])) {
            Oenologist::where('user_id', $user->id)->findOrFail($validated['oenologist_id']);
        }

        $oldData = [
            'wine_id'        => $bottling->wine_id,
            'container_id'   => $bottling->container_id,
            'quantity_liters'=> $bottling->quantity_liters,
        ];

        DB::transaction(function () use ($bottling, $validated, $oldData) {
            $bottling->update($validated);
            app(WineContainerStockService::class)->updateBottling($bottling->fresh(), $oldData);
        });

        $bottling->load(['wine', 'container', 'productLot', 'oenologist', 'supplies']);

        return response()->json(['data' => new BottlingResource($bottling)]);
    }

    // ─── DELETE /winery/bottlings/{id} ───────────────────────────────────────

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $bottling = WineBottling::forUser($user->id)->findOrFail($id);

        DB::transaction(function () use ($bottling) {
            app(WineContainerStockService::class)->revertBottling($bottling);
            $bottling->supplies()->delete();
            $bottling->delete();
        });

        return response()->json(['message' => __('Embotellado eliminado correctamente.')]);
    }
}
