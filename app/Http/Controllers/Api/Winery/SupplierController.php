<?php

namespace App\Http\Controllers\Api\Winery;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $request->validate([
            'category' => 'nullable|string|in:' . implode(',', array_keys(Supplier::CATEGORIES)),
            'per_page' => ['nullable', function ($attr, $val, $fail) {
                if ($val !== 'all' && (!is_numeric($val) || (int) $val < 1 || (int) $val > 100)) {
                    $fail('per_page debe ser un entero entre 1 y 100, o "all".');
                }
            }],
        ]);

        $query = Supplier::forUser($user->id);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if (!$request->boolean('include_inactive', false)) {
            $query->active();
        }

        $perPage   = $this->resolvePerPage($request, 50, 100);
        $suppliers = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'data' => $suppliers->map(fn ($s) => [
                'id'             => $s->id,
                'name'           => $s->name,
                'contact_person' => $s->contact_person,
                'email'          => $s->email,
                'phone'          => $s->phone,
                'address'        => $s->address,
                'vat_number'     => $s->vat_number,
                'category'       => $s->category,
                'category_label' => $s->category_label,
                'active'         => $s->active,
                'notes'          => $s->notes,
                'created_at'     => $s->created_at->toIso8601String(),
            ]),
            'meta' => [
                'total'        => $suppliers->total(),
                'per_page'     => $suppliers->perPage(),
                'current_page' => $suppliers->currentPage(),
                'last_page'    => $suppliers->lastPage(),
                'categories'   => Supplier::CATEGORIES,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'email'          => 'nullable|email|max:150',
            'phone'          => 'nullable|string|max:30',
            'address'        => 'nullable|string|max:500',
            'vat_number'     => 'nullable|string|max:30',
            'category'       => 'nullable|string|in:' . implode(',', array_keys(Supplier::CATEGORIES)),
            'notes'          => 'nullable|string|max:1000',
        ]);

        $supplier = Supplier::create([...$validated, 'user_id' => $user->id, 'active' => true]);

        return response()->json([
            'data'    => $supplier,
            'message' => 'Proveedor creado correctamente.',
        ], 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supplier = Supplier::forUser($user->id)->findOrFail($id);

        return response()->json(['data' => $supplier]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supplier = Supplier::forUser($user->id)->findOrFail($id);

        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'contact_person' => 'sometimes|nullable|string|max:150',
            'email'          => 'sometimes|nullable|email|max:150',
            'phone'          => 'sometimes|nullable|string|max:30',
            'address'        => 'sometimes|nullable|string|max:500',
            'vat_number'     => 'sometimes|nullable|string|max:30',
            'category'       => 'sometimes|nullable|string|in:' . implode(',', array_keys(Supplier::CATEGORIES)),
            'active'         => 'sometimes|boolean',
            'notes'          => 'sometimes|nullable|string|max:1000',
        ]);

        $supplier->update($validated);

        return response()->json(['data' => $supplier->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $user     = $request->user();
        abort_unless($user->hasWineryAccess(), 403);

        $supplier = Supplier::forUser($user->id)->findOrFail($id);
        $supplier->update(['active' => false]);

        return response()->json(['message' => 'Proveedor desactivado correctamente.']);
    }
}
