<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BiodiversityRecordResource;
use App\Models\BiodiversityRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BiodiversityRecordController extends Controller
{
    // ─── GET /viticulturist/biodiversity-records ──────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer',
            'plot_id'     => 'nullable|integer',
            'record_type' => 'nullable|string',
            'search'      => 'nullable|string|max:100',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = BiodiversityRecord::where('viticulturist_id', $user->id)
            ->with(['plot'])
            ->orderByDesc('record_date');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('description', 'like', "%{$term}%")
                  ->orWhere('species', 'like', "%{$term}%");
            });
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => BiodiversityRecordResource::collection($items->items()),
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
            'plot_id'     => 'required|integer|exists:plots,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'record_type' => 'required|string|max:100',
            'description' => 'required|string|max:500',
            'area_m2'     => 'nullable|numeric|min:0',
            'species'     => 'nullable|string|max:255',
            'record_date' => 'required|date',
            'notes'       => 'nullable|string|max:2000',
        ]);

        \App\Models\Plot::where('user_id', $user->id)->findOrFail($validated['plot_id']);

        $record = \App\Models\BiodiversityRecord::create([...$validated, 'viticulturist_id' => $user->id]);
        $record->load(['plot']);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\BiodiversityRecordResource($record),
            'message' => 'Registro de biodiversidad creado correctamente.',
        ], 201);
    }
}
