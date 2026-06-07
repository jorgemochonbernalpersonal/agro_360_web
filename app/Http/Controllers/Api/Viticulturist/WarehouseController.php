<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\WarehouseStockResource;
use App\Models\ProductStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'warehouse_id' => 'nullable|integer|exists:warehouses,id',
            'product_type' => 'nullable|string|max:100',
        ]);

        $query = ProductStock::whereHas('warehouse', fn ($q) => $q->where('user_id', $user->id))
            ->with(['warehouse'])
            ->orderByDesc('updated_at');

        if ($request->filled('search')) {
            $query->where('product_name', 'LIKE', '%'.$request->search.'%');
        }

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, WarehouseStockResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $record = \App\Models\Warehouse::create([...$validated, 'user_id' => $user->id, 'active' => true]);

        return response()->json([
            'data' => $record,
            'message' => __('Almacén registrado correctamente.'),
        ], 201);
    }
}
