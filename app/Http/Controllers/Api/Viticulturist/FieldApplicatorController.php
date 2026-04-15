<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FieldApplicatorResource;
use App\Models\FieldApplicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldApplicatorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'search'        => 'nullable|string|max:255',
            'campaign_id'   => 'nullable|integer|exists:campaigns,id',
            'ropo_category' => 'nullable|string|max:100',
        ]);

        $query = FieldApplicator::active()
            ->forViticulturist($user->id)
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = '%' . $request->search . '%';
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

        return response()->json([
            'data' => FieldApplicatorResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
