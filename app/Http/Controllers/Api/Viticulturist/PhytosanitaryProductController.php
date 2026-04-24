<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PhytosanitaryProductResource;
use App\Models\PhytosanitaryProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhytosanitaryProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'type'   => 'nullable|string|max:100',
        ]);

        $query = PhytosanitaryProduct::forUser($user->id)
            ->where('active', true)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('active_ingredient', 'LIKE', $search)
                  ->orWhere('registration_number', 'LIKE', $search);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PhytosanitaryProductResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'name'                     => 'required|string|max:255',
            'active_ingredient'        => 'nullable|string|max:255',
            'registration_number'      => 'required|string|max:100',
            'registration_status'      => 'nullable|string|in:active,expired,revoked',
            'type'                     => 'nullable|string|max:100',
            'withdrawal_period_days'   => 'required|integer|min:0',
            'description'              => 'nullable|string|max:1000',
        ]);

        $record = \App\Models\PhytosanitaryProduct::create([...$validated, 'user_id' => $user->id, 'active' => true]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\PhytosanitaryProductResource($record),
            'message' => 'Producto fitosanitario registrado correctamente.',
        ], 201);
    }
}
