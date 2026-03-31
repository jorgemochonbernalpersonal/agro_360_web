<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\WinerySupply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WinerySupplyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'supply_type' => 'nullable|string|in:' . implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'low_stock'   => 'nullable|boolean',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = WinerySupply::forUser($user->id)->with('unitOfMeasurement');

        if ($request->filled('supply_type')) {
            $query->where('supply_type', $request->supply_type);
        }
        if (!$request->boolean('include_inactive', false)) {
            $query->active();
        }
        if ($request->boolean('low_stock', false)) {
            $query->whereRaw('min_stock_alert IS NOT NULL AND current_stock <= min_stock_alert');
        }

        $perPage   = min($request->integer('per_page', 50), 100);
        $supplies  = $query->orderBy('name')->paginate($perPage);

        $lowStockCount = WinerySupply::forUser($user->id)->active()
            ->whereRaw('min_stock_alert IS NOT NULL AND current_stock <= min_stock_alert')
            ->count();

        return response()->json([
            'data' => $supplies->map(fn ($s) => $this->format($s)),
            'meta' => [
                'total'           => $supplies->total(),
                'per_page'        => $supplies->perPage(),
                'last_page'       => $supplies->lastPage(),
                'low_stock_count' => $lowStockCount,
                'supply_types'    => WinerySupply::SUPPLY_TYPES,
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user   = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supply = WinerySupply::forUser($user->id)->with('unitOfMeasurement')->findOrFail($id);

        return response()->json(['data' => $this->format($supply)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'                   => 'required|string|max:255',
            'commercial_name'        => 'nullable|string|max:255',
            'supply_type'            => 'required|string|in:' . implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'unit_of_measurement_id' => 'nullable|integer|exists:unit_of_measurements,id',
            'current_stock'          => 'nullable|numeric|min:0',
            'min_stock_alert'        => 'nullable|numeric|min:0',
            'expiry_date'            => 'nullable|date',
            'notes'                  => 'nullable|string|max:1000',
        ]);

        $supply = WinerySupply::create([...$validated, 'user_id' => $user->id, 'active' => true]);
        $supply->load('unitOfMeasurement');

        return response()->json([
            'data'    => $this->format($supply),
            'message' => 'Insumo creado correctamente.',
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user   = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supply = WinerySupply::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'                   => 'sometimes|string|max:255',
            'commercial_name'        => 'sometimes|nullable|string|max:255',
            'supply_type'            => 'sometimes|string|in:' . implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'unit_of_measurement_id' => 'sometimes|nullable|integer|exists:unit_of_measurements,id',
            'current_stock'          => 'sometimes|nullable|numeric|min:0',
            'min_stock_alert'        => 'sometimes|nullable|numeric|min:0',
            'expiry_date'            => 'sometimes|nullable|date',
            'active'                 => 'sometimes|boolean',
            'notes'                  => 'sometimes|nullable|string|max:1000',
        ]);

        $supply->update($validated);
        $supply->load('unitOfMeasurement');

        return response()->json(['data' => $this->format($supply)]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user   = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supply = WinerySupply::forUser($user->id)->findOrFail($id);
        $supply->update(['active' => false]);

        return response()->json(['message' => 'Insumo desactivado correctamente.']);
    }

    private function format(WinerySupply $s): array
    {
        return [
            'id'              => $s->id,
            'name'            => $s->name,
            'commercial_name' => $s->commercial_name,
            'supply_type'     => $s->supply_type,
            'type_label'      => $s->type_label,
            'unit'            => $s->unitOfMeasurement ? [
                'id'     => $s->unitOfMeasurement->id,
                'name'   => $s->unitOfMeasurement->name,
                'symbol' => $s->unitOfMeasurement->symbol ?? null,
            ] : null,
            'current_stock'   => $s->current_stock !== null ? (float) $s->current_stock : null,
            'min_stock_alert' => $s->min_stock_alert !== null ? (float) $s->min_stock_alert : null,
            'is_low_stock'    => $s->isLowStock(),
            'expiry_date'     => $s->expiry_date?->toDateString(),
            'active'          => $s->active,
            'notes'           => $s->notes,
            'created_at'      => $s->created_at->toIso8601String(),
        ];
    }
}
