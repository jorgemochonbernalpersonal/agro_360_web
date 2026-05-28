<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\ExternalGrapePurchase;
use App\Models\Supplier;
use App\Models\Wine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalGrapePurchaseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $query = ExternalGrapePurchase::forUser($user->id)
            ->with(['supplier', 'destinationContainer', 'wine'])
            ->orderByDesc('purchase_date');

        if ($request->filled('product_type')) {
            $query->where('product_type', $request->input('product_type'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('vintage_year')) {
            $query->forYear($request->integer('vintage_year'));
        }
        if ($request->filled('wine_id')) {
            $query->where('wine_id', $request->integer('wine_id'));
        }

        $perPage = $this->resolvePerPage($request, 20, 100);
        $items   = $query->paginate($perPage);

        return response()->json([
            'data' => $items->map(fn ($p) => $this->format($p)),
            'meta' => [
                'total'        => $items->total(),
                'per_page'     => $items->perPage(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'product_types'=> ExternalGrapePurchase::productTypeOptions(),
                'statuses'     => ExternalGrapePurchase::statusOptions(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $purchase = ExternalGrapePurchase::forUser($user->id)
            ->with(['supplier', 'destinationContainer', 'wine'])
            ->findOrFail($id);

        return response()->json(['data' => $this->format($purchase)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'supplier_id'              => 'nullable|integer|exists:suppliers,id',
            'destination_container_id' => 'nullable|integer|exists:containers,id',
            'wine_id'                  => 'nullable|integer|exists:wines,id',
            'supplier_name'            => 'nullable|string|max:255',
            'product_type'             => 'required|string|in:' . implode(',', array_keys(ExternalGrapePurchase::PRODUCT_TYPES)),
            'variety'                  => 'nullable|string|max:100',
            'origin'                   => 'nullable|string|max:255',
            'vintage_year'             => 'nullable|integer|min:1900|max:2100',
            'quantity_kg'              => 'required|numeric|min:0.001',
            'price_per_kg'             => 'nullable|numeric|min:0',
            'total_price'              => 'nullable|numeric|min:0',
            'purchase_date'            => 'required|date',
            'delivery_date'            => 'nullable|date',
            'rega_code'                => 'nullable|string|max:100',
            'quality_certificate'      => 'nullable|string|max:255',
            'baume_degree'             => 'nullable|numeric|min:0|max:30',
            'brix_degree'              => 'nullable|numeric|min:0|max:50',
            'potential_alcohol'        => 'nullable|numeric|min:0|max:25',
            'acidity_level'            => 'nullable|numeric|min:0',
            'ph_level'                 => 'nullable|numeric|min:0|max:14',
            'status'                   => 'nullable|string|in:' . implode(',', array_keys(ExternalGrapePurchase::STATUSES)),
            'notes'                    => 'nullable|string|max:1000',
        ]);

        if (isset($validated['supplier_id'])) {
            Supplier::forUser($user->id)->findOrFail($validated['supplier_id']);
        }
        if (isset($validated['destination_container_id'])) {
            Container::where('user_id', $user->id)->findOrFail($validated['destination_container_id']);
        }
        if (isset($validated['wine_id'])) {
            Wine::forUser($user->id)->findOrFail($validated['wine_id']);
        }

        // Auto-calcular total si no se proporciona
        if (!isset($validated['total_price']) && isset($validated['price_per_kg'])) {
            $validated['total_price'] = round($validated['quantity_kg'] * $validated['price_per_kg'], 2);
        }

        $validated['user_id']    = $user->id;
        $validated['status']   ??= 'pending';
        $validated['created_by'] = $user->id;

        $purchase = ExternalGrapePurchase::create($validated);
        $purchase->load(['supplier', 'destinationContainer', 'wine']);

        return response()->json([
            'data'    => $this->format($purchase),
            'message' => __('Compra de uva/mosto externa registrada correctamente.'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $purchase = ExternalGrapePurchase::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'supplier_id'              => 'sometimes|nullable|integer|exists:suppliers,id',
            'destination_container_id' => 'sometimes|nullable|integer|exists:containers,id',
            'wine_id'                  => 'sometimes|nullable|integer|exists:wines,id',
            'supplier_name'            => 'sometimes|nullable|string|max:255',
            'product_type'             => 'sometimes|string|in:' . implode(',', array_keys(ExternalGrapePurchase::PRODUCT_TYPES)),
            'variety'                  => 'sometimes|nullable|string|max:100',
            'origin'                   => 'sometimes|nullable|string|max:255',
            'vintage_year'             => 'sometimes|nullable|integer|min:1900|max:2100',
            'quantity_kg'              => 'sometimes|numeric|min:0.001',
            'price_per_kg'             => 'sometimes|nullable|numeric|min:0',
            'total_price'              => 'sometimes|nullable|numeric|min:0',
            'purchase_date'            => 'sometimes|date',
            'delivery_date'            => 'sometimes|nullable|date',
            'rega_code'                => 'sometimes|nullable|string|max:100',
            'quality_certificate'      => 'sometimes|nullable|string|max:255',
            'baume_degree'             => 'sometimes|nullable|numeric|min:0|max:30',
            'brix_degree'              => 'sometimes|nullable|numeric|min:0|max:50',
            'potential_alcohol'        => 'sometimes|nullable|numeric|min:0|max:25',
            'acidity_level'            => 'sometimes|nullable|numeric|min:0',
            'ph_level'                 => 'sometimes|nullable|numeric|min:0|max:14',
            'status'                   => 'sometimes|string|in:' . implode(',', array_keys(ExternalGrapePurchase::STATUSES)),
            'notes'                    => 'sometimes|nullable|string|max:1000',
        ]);

        // Auto-recalculate total_price when quantity or price changes but total not explicitly provided
        if (!isset($validated['total_price']) && (isset($validated['quantity_kg']) || isset($validated['price_per_kg']))) {
            $qty   = $validated['quantity_kg'] ?? $purchase->quantity_kg;
            $price = $validated['price_per_kg'] ?? $purchase->price_per_kg;
            if ($price !== null) {
                $validated['total_price'] = round($qty * $price, 2);
            }
        }

        $purchase->update($validated);
        $purchase->load(['supplier', 'destinationContainer', 'wine']);

        return response()->json(['data' => $this->format($purchase)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $purchase = ExternalGrapePurchase::forUser($user->id)->findOrFail($id);
        $purchase->delete();

        return response()->json(['message' => __('Compra externa eliminada correctamente.')]);
    }

    private function format(ExternalGrapePurchase $p): array
    {
        return [
            'id'                       => $p->id,
            'supplier_id'              => $p->supplier_id,
            'supplier_display'         => $p->supplier_display,
            'destination_container'    => $p->destinationContainer ? ['id' => $p->destinationContainer->id, 'name' => $p->destinationContainer->name] : null,
            'wine_id'                  => $p->wine_id,
            'wine_name'                => $p->wine?->name,
            'product_type'             => $p->product_type,
            'product_type_label'       => $p->product_type_label,
            'variety'                  => $p->variety,
            'origin'                   => $p->origin,
            'vintage_year'             => $p->vintage_year,
            'quantity_kg'              => (float) $p->quantity_kg,
            'price_per_kg'             => $p->price_per_kg !== null ? (float) $p->price_per_kg : null,
            'total_price'              => $p->total_price !== null ? (float) $p->total_price : null,
            'purchase_date'            => $p->purchase_date?->toDateString(),
            'delivery_date'            => $p->delivery_date?->toDateString(),
            'rega_code'                => $p->rega_code,
            'quality_certificate'      => $p->quality_certificate,
            'baume_degree'             => $p->baume_degree !== null ? (float) $p->baume_degree : null,
            'brix_degree'              => $p->brix_degree !== null ? (float) $p->brix_degree : null,
            'potential_alcohol'        => $p->potential_alcohol !== null ? (float) $p->potential_alcohol : null,
            'acidity_level'            => $p->acidity_level !== null ? (float) $p->acidity_level : null,
            'ph_level'                 => $p->ph_level !== null ? (float) $p->ph_level : null,
            'status'                   => $p->status,
            'status_label'             => $p->status_label,
            'notes'                    => $p->notes,
            'created_at'               => $p->created_at->toIso8601String(),
        ];
    }
}
