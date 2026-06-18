<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Winery\StoreSupplierRequest;
use App\Http\Requests\Api\Winery\UpdateSupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'category' => 'nullable|string|in:'.implode(',', array_keys(Supplier::CATEGORIES)),
            'per_page' => ['nullable', function ($attr, $val, $fail) {
                if ($val !== 'all' && (! is_numeric($val) || (int) $val < 1 || (int) $val > 100)) {
                    $fail('per_page debe ser un entero entre 1 y 100, o "all".');
                }
            }],
        ]);

        $query = Supplier::forUser($user->id);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if (! $request->boolean('include_inactive', false)) {
            $query->active();
        }

        $perPage = $this->resolvePerPage($request, 50, 100);
        $suppliers = $query->orderBy('name')->paginate($perPage);

        return $this->paginated($suppliers, $suppliers->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'contact_person' => $s->contact_person,
            'email' => $s->email,
            'phone' => $s->phone,
            'address' => $s->address,
            'vat_number' => $s->vat_number,
            'category' => $s->category,
            'category_label' => $s->category_label,
            'active' => $s->active,
            'notes' => $s->notes,
            'created_at' => $s->created_at->toIso8601String(),
        ]), [
            'categories' => Supplier::CATEGORIES,
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validated();

        $supplier = Supplier::create([...$validated, 'user_id' => $user->id, 'active' => true]);

        return $this->created($supplier);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supplier = Supplier::forUser($user->id)->findOrFail($id);

        return $this->success($supplier);
    }

    public function update(UpdateSupplierRequest $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supplier = Supplier::forUser($user->id)->findOrFail($id);

        $validated = $request->validated();

        $supplier->update($validated);

        return $this->success($supplier->fresh());
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $supplier = Supplier::forUser($user->id)->findOrFail($id);
        $supplier->update(['active' => false]);

        return $this->deleted(__('Proveedor desactivado correctamente.'));
    }
}
