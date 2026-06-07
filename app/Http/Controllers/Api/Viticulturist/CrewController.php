<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\CrewResource;
use App\Models\Crew;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrewController extends BaseApiController
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
            $query->where('name', 'LIKE', '%'.$request->search.'%');
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, CrewResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $record = \App\Models\Crew::create([...$validated, 'viticulturist_id' => $user->id]);

        return response()->json([
            'data' => $record,
            'message' => __('Cuadrilla registrada correctamente.'),
        ], 201);
    }
}
