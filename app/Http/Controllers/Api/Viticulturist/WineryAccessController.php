<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Models\WineryViticulturist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryAccessController extends Controller
{
    // ─── GET /viticulturist/winery-access ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $links = WineryViticulturist::where('viticulturist_id', $user->id)
            ->whereNotNull('winery_id')
            ->with('winery')
            ->get()
            ->map(fn ($link) => [
                'id'                 => $link->id,
                'winery_id'          => $link->winery_id,
                'winery_name'        => $link->winery?->name,
                'source'             => $link->source,
                'notebook_access'    => (bool) $link->notebook_access,
                'notebook_granted_at' => $link->notebook_granted_at?->toIso8601String(),
                'created_at'         => $link->created_at->toIso8601String(),
            ]);

        return response()->json([
            'data' => $links,
        ]);
    }
}
