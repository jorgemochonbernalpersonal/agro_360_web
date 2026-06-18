<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreWinerySupplyRequest;
use App\Http\Requests\Api\Winery\UpdateWinerySupplyRequest;
use App\Models\WinerySupply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WinerySupplyController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'supply_type' => 'nullable|string|in:'.implode(',', array_keys(WinerySupply::SUPPLY_TYPES)),
            'low_stock' => 'nullable|boolean',
            'per_page' => ['nullable', function ($attr, $val, $fail) {
                if ($val !== 'all' && (! is_numeric($val) || (int) $val < 1 || (int) $val > 100)) {
                    $fail('per_page debe ser un entero entre 1 y 100, o "all".');
                }
            }],
        ]);

        $query = WinerySupply::forUser($user->id)->with('unitOfMeasurement');

        if ($request->filled('supply_type')) {
            $query->where('supply_type', $request->supply_type);
        }
        if (! $request->boolean('include_inactive', false)) {
            $query->active();
        }
        if ($request->boolean('low_stock', false)) {
            $query->whereRaw('min_stock_alert IS NOT NULL AND current_stock <= min_stock_alert');
        }

        $perPage = $this->resolvePerPage($request, 50, 100);
        $supplies = $query->orderBy('name')->paginate($perPage);

        $lowStockCount = WinerySupply::forUser($user->id)->active()
            ->whereRaw('min_stock_alert IS NOT NULL AND current_stock <= min_stock_alert')
            ->count();

        return $this->paginated($supplies, $supplies->map(fn ($s) => $this->format($s)), [
            'low_stock_count' => $lowStockCount,
            'supply_types' => WinerySupply::SUPPLY_TYPES,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supply = WinerySupply::forUser($user->id)->with('unitOfMeasurement')->findOrFail($id);

        return $this->success($this->format($supply));
    }

    public function store(StoreWinerySupplyRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $supply = WinerySupply::create([...$validated, 'user_id' => $user->id, 'active' => true]);
        $supply->load('unitOfMeasurement');

        return $this->created($this->format($supply));
    }

    public function update(UpdateWinerySupplyRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supply = WinerySupply::forUser($user->id)->findOrFail($id);

        $validated = $request->validated();

        $supply->update($validated);
        $supply->load('unitOfMeasurement');

        return $this->success($this->format($supply));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supply = WinerySupply::forUser($user->id)->findOrFail($id);
        $supply->update(['active' => false]);

        return $this->deleted(__('Insumo desactivado correctamente.'));
    }

    private function format(WinerySupply $s): array
    {
        return [
            'id' => $s->id,
            'name' => $s->name,
            'commercial_name' => $s->commercial_name,
            'supply_type' => $s->supply_type,
            'type_label' => $s->type_label,
            'unit' => $s->unitOfMeasurement ? [
                'id' => $s->unitOfMeasurement->id,
                'name' => $s->unitOfMeasurement->name,
                'symbol' => $s->unitOfMeasurement->symbol ?? null,
            ] : null,
            'current_stock' => $s->current_stock !== null ? (float) $s->current_stock : null,
            'min_stock_alert' => $s->min_stock_alert !== null ? (float) $s->min_stock_alert : null,
            'is_low_stock' => $s->isLowStock(),
            'expiry_date' => $s->expiry_date?->toDateString(),
            'active' => $s->active,
            'notes' => $s->notes,
            'created_at' => $s->created_at->toIso8601String(),
        ];
    }
}
