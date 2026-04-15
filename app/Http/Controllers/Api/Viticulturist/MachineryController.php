<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MachineryResource;
use App\Models\Machinery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'type'   => 'nullable|string|max:100',
        ]);

        $query = Machinery::forViticulturist($user->id)
            ->active()
            ->withCount(['activities'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                  ->orWhere('brand', 'LIKE', $search)
                  ->orWhere('model', 'LIKE', $search);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => MachineryResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
