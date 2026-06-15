<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Api\Viticulturist\IndexContainerReturnRequest;
use App\Http\Requests\Api\Viticulturist\StoreContainerReturnRequest;
use App\Http\Resources\Api\ContainerReturnResource;
use App\Models\PhytosanitaryContainerReturn;
use Illuminate\Http\JsonResponse;

class ContainerReturnController extends BaseApiController
{
    // ─── GET /viticulturist/container-returns ────────────────────────────────

    public function index(IndexContainerReturnRequest $request): JsonResponse
    {
        $query = PhytosanitaryContainerReturn::forViticulturist($request->user()->id)
            ->active()
            ->with('phytosanitaryProduct')
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->forCampaign($request->campaign_id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('product_name', 'like', "%{$term}%")
                    ->orWhere('registration_number', 'like', "%{$term}%")
                    ->orWhere('collection_point', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, ContainerReturnResource::collection($items->items()));
    }

    // ─── POST /viticulturist/container-returns ──────────────────────────────

    public function store(StoreContainerReturnRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = \App\Models\Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)->value('id');
        }

        $record = PhytosanitaryContainerReturn::create([
            ...$validated,
            'viticulturist_id' => $user->id,
            'active' => true,
        ]);

        $record->load('phytosanitaryProduct');

        return response()->json([
            'data' => new ContainerReturnResource($record),
            'message' => __('Retorno de envase registrado correctamente.'),
        ], 201);
    }
}
