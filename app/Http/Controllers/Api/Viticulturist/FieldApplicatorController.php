<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\FieldApplicatorResource;
use App\Models\FieldApplicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldApplicatorController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'ropo_category' => 'nullable|string|max:100',
        ]);

        $query = FieldApplicator::active()
            ->forViticulturist($user->id)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = '%'.$request->search.'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', $search)
                    ->orWhere('ropo_number', 'LIKE', $search);
            });
        }

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('ropo_category')) {
            $query->where('ropo_category', $request->ropo_category);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, FieldApplicatorResource::collection($items->items()));
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $validated = $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'name' => 'required|string|max:255',
            'ropo_number' => 'required|string|max:30',
            'ropo_category' => 'nullable|in:basic,qualified,fumigator,pilot',
            'ropo_expiry_date' => 'nullable|date',
            'is_advisor' => 'nullable|boolean',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $record = \App\Models\FieldApplicator::create([...$validated, 'viticulturist_id' => $user->id, 'active' => true]);

        return response()->json([
            'data' => new \App\Http\Resources\Api\FieldApplicatorResource($record),
            'message' => __('Aplicador ROPO registrado correctamente.'),
        ], 201);
    }
}
