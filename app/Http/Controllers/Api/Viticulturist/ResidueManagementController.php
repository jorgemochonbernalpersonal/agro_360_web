<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ResidueManagementResource;
use App\Models\ResidueManagement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidueManagementController extends Controller
{
    // ─── GET /viticulturist/residue-managements ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'   => 'nullable|integer|min:1',
            'practice_type' => 'nullable|string|max:50',
            'material_type' => 'nullable|string|max:50',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ]);

        $query = ResidueManagement::active()
            ->forViticulturist($user->id)
            ->with('plot')
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', (int) $request->campaign_id);
        }

        if ($request->filled('practice_type')) {
            $query->where('practice_type', $request->practice_type);
        }

        if ($request->filled('material_type')) {
            $query->where('material_type', $request->material_type);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => ResidueManagementResource::collection($items->items()),
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
            'campaign_id'         => 'nullable|integer|exists:campaigns,id',
            'plot_id'             => 'nullable|integer|exists:plots,id',
            'date'                => 'required|date',
            'practice_type'       => 'required|string|max:100',
            'material_type'       => 'required|string|max:100',
            'estimated_quantity'  => 'nullable|numeric|min:0',
            'quantity_unit'       => 'nullable|string|max:50',
            'notes'               => 'nullable|string|max:2000',
        ]);

        if (isset($validated['plot_id'])) {
            \App\Models\Plot::where('viticulturist_id', $user->id)->findOrFail($validated['plot_id']);
        }

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = \App\Models\Campaign::getOrCreateActiveForYear($user->id)?->id;
        }

        if (empty($validated['campaign_id'])) {
            return response()->json(['error' => __('No hay una campaña activa. Activa una campaña antes de registrar.')], 422);
        }

        $record = \App\Models\ResidueManagement::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\ResidueManagementResource($record),
            'message' => __('Gestión de residuos registrada correctamente.'),
        ], 201);
    }
}
