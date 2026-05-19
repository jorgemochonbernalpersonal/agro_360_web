<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CampaignDocumentResource;
use App\Models\Campaign;
use App\Models\CampaignDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CampaignDocumentController extends Controller
{
    // ─── GET /viticulturist/campaign-documents ────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id'   => 'nullable|integer',
            'document_type' => 'nullable|string',
            'search'        => 'nullable|string|max:100',
            'per_page'      => 'nullable|integer|min:1|max:100',
        ]);

        $query = CampaignDocument::forViticulturist($user->id)
            ->with(['campaign'])
            ->orderByDesc('created_at');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => CampaignDocumentResource::collection($items->items()),
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
            'campaign_id'    => 'nullable|integer|exists:campaigns,id',
            'name'           => 'required|string|max:255',
            'document_type'  => 'required|string|max:100',
            'notes'          => 'nullable|string|max:2000',
        ]);

        if (empty($validated['campaign_id'])) {
            $validated['campaign_id'] = Campaign::where('viticulturist_id', $user->id)
                ->where('active', true)
                ->value('id');
        }

        $record = \App\Models\CampaignDocument::create([...$validated, 'viticulturist_id' => $user->id]);

        return response()->json([
            'data'    => new \App\Http\Resources\Api\CampaignDocumentResource($record),
            'message' => 'Documento registrado correctamente.',
        ], 201);
    }
}
