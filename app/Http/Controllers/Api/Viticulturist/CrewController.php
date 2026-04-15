<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CrewResource;
use App\Models\Crew;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $query = Crew::forViticulturist($user->id)
            ->withCount(['members', 'activities'])
            ->orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => CrewResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
