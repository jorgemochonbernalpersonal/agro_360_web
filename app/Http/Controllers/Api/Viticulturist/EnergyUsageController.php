<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\EnergyUsageResource;
use App\Models\Campaign;
use App\Models\EnergyUsage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnergyUsageController extends Controller
{
    // ─── GET /viticulturist/energy-usages ─────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'  => 'nullable|integer',
            'energy_type'  => 'nullable|string',
            'search'       => 'nullable|string|max:100',
            'per_page'     => 'nullable|integer|min:1|max:100',
        ]);

        $query = EnergyUsage::active()
            ->forViticulturist($user->id)
            ->with(['machinery'])
            ->orderByDesc('date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('energy_type')) {
            $query->where('energy_type', $request->energy_type);
        }

        if ($request->filled('search')) {
            $query->where('usage_description', 'like', "%{$request->search}%");
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => EnergyUsageResource::collection($items->items()),
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
            'campaign_id'       => 'nullable|integer|exists:campaigns,id',
            'machinery_id'      => 'nullable|integer|exists:machinery,id',
            'date'              => 'required|date',
            'energy_type'       => 'required|in:diesel,gasoline,electricity,lpg,natural_gas,water_pump,other',
            'unit'              => 'required|in:liters,kwh,m3,kg',
            'quantity'          => 'required|numeric|min:0',
            'total_cost'        => 'nullable|numeric|min:0',
            'co2_kg_equivalent' => 'nullable|numeric|min:0',
            'usage_description' => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = \App\Models\EnergyUsage::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\EnergyUsageResource($record),
            'message' => __('Consumo energético registrado correctamente.'),
        ], 201);
    }
}
