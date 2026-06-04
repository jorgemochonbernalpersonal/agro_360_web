<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AnnouncementResource;
use App\Models\WineryAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    // ─── GET /viticulturist/announcements ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $items = WineryAnnouncement::active()
            ->visibleTo($user)
            ->with('winery')
            ->orderByDesc('published_at')
            ->paginate($this->resolvePerPage($request, 20));

        return response()->json([
            'data' => AnnouncementResource::collection($items->items()),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/announcements/{id}/read ────────────────────────

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $announcement = WineryAnnouncement::active()->visibleTo($user)->findOrFail($id);
        $announcement->viticulturists()->updateExistingPivot($user->id, ['read_at' => now()]);

        return response()->json(['message' => __('Anuncio marcado como leído.')]);
    }
}
