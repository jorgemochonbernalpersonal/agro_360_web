<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlotEnvironmentResource;
use App\Models\PlotEnvironment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlotEnvironmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'plot_id'     => 'nullable|integer|exists:plots,id',
        ]);

        $query = PlotEnvironment::forViticulturist($user->id)
            ->with(['plot'])
            ->orderByDesc('created_at');

        if ($request->filled('campaign_id')) {
            $query->where('campaign_id', $request->campaign_id);
        }

        if ($request->filled('plot_id')) {
            $query->where('plot_id', $request->plot_id);
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => PlotEnvironmentResource::collection($items->items()),
            'meta' => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }
}
