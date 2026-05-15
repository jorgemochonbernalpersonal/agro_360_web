<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ContainerReturnResource;
use App\Models\PhytosanitaryContainerReturn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContainerReturnController extends Controller
{
    // ─── GET /viticulturist/container-returns ────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = PhytosanitaryContainerReturn::forViticulturist($user->id)
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

        return response()->json([
            'data' => ContainerReturnResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/container-returns ──────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id'              => 'nullable|integer|exists:campaigns,id',
            'phytosanitary_product_id' => 'nullable|integer|exists:phytosanitary_products,id',
            'date'                     => 'required|date',
            'product_name'             => 'required|string|max:255',
            'registration_number'      => 'nullable|string|max:100',
            'container_type'           => 'required|string|in:plastic,glass,metal,cardboard,flexible,other',
            'container_size_liters'    => 'nullable|numeric|min:0',
            'containers_quantity'      => 'required|integer|min:1',
            'total_weight_kg'          => 'nullable|numeric|min:0',
            'collection_system'        => 'nullable|string|in:sigfito,field,other',
            'collection_point'         => 'nullable|string|max:255',
            'transport_document'       => 'nullable|string|max:100',
            'notes'                    => 'nullable|string|max:2000',
        ]);

        $record = PhytosanitaryContainerReturn::create([
            ...$validated,
            'viticulturist_id' => $user->id,
            'active'           => true,
        ]);

        $record->load('phytosanitaryProduct');

        return response()->json([
            'data'    => new ContainerReturnResource($record),
            'message' => 'Retorno de envase registrado correctamente.',
        ], 201);
    }
}
