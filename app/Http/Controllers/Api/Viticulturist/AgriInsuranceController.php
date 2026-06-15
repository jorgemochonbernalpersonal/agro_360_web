<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\AgriInsuranceResource;
use App\Models\AgriInsurance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgriInsuranceController extends BaseApiController
{
    // ─── GET /viticulturist/agri-insurances ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'status' => 'nullable|string|in:pending,active,expired,cancelled',
            'search' => 'nullable|string|max:100',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AgriInsurance::where('viticulturist_id', $user->id)
            ->orderByDesc('start_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('policy_number', 'like', "%{$term}%")
                    ->orWhere('insurance_company', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, AgriInsuranceResource::collection($items->items()));
    }

    // ─── POST /viticulturist/agri-insurances ────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'policy_number' => 'nullable|string|max:100',
            'insurance_company' => 'required|string|max:255',
            'coverage_type' => 'required|string|in:frost,hail,drought,flood,fire,pest,comprehensive,other',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'insured_amount' => 'nullable|numeric|min:0',
            'premium' => 'nullable|numeric|min:0',
            'subsidy_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:pending,active,expired,cancelled',
            'agent_name' => 'nullable|string|max:255',
            'agent_phone' => 'nullable|string|max:50',
            'covered_plots' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:2000',
        ]);

        $record = AgriInsurance::create([
            ...$validated,
            'viticulturist_id' => $user->id,
            'status' => $validated['status'] ?? 'pending',
        ]);

        return response()->json([
            'data' => new AgriInsuranceResource($record),
            'message' => __('Seguro agrario registrado correctamente.'),
        ], 201);
    }
}
