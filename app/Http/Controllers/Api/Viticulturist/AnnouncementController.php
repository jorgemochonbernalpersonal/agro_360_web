<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\Api\AnnouncementResource;
use App\Models\WineryAnnouncement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends BaseApiController
{
    // ─── GET /viticulturist/announcements ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $items = WineryAnnouncement::active()
            ->visibleTo($user)
            ->with('winery')
            ->orderByDesc('published_at')
            ->paginate($this->resolvePerPage($request, 20));

        return $this->paginated($items, AnnouncementResource::collection($items->items()));
    }

    // ─── POST /viticulturist/announcements/{id}/read ────────────────────────

    public function markRead(Request $request, int $id): JsonResponse
    {
        $user = $request->user();

        $announcement = WineryAnnouncement::active()->visibleTo($user)->findOrFail($id);
        $announcement->viticulturists()->updateExistingPivot($user->id, ['read_at' => now()]);

        return $this->deleted(__('Anuncio marcado como leído.'));
    }
}
