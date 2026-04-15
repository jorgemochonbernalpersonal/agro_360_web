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
}
