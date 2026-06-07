<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    // ─── GET /viticulturist/notifications ────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'unread_only' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = $user->notifications()->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        $items = $query->paginate($this->resolvePerPage($request, 20));

        $data = $items->getCollection()->map(fn ($n) => [
            'id' => $n->id,
            'type' => class_basename($n->type),
            'data' => $n->data,
            'read_at' => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at->toIso8601String(),
        ]);

        return response()->json([
            'data' => $data,
            'unread_count' => $user->unreadNotifications()->count(),
            'meta' => [
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'has_more' => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/notifications/{id}/read ────────────────────────

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $user->notifications()->where('id', $id)->update(['read_at' => now()]);

        return $this->deleted(__('Notificación marcada como leída.'));
    }

    // ─── POST /viticulturist/notifications/read-all ─────────────────────────

    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $user->unreadNotifications()->update(['read_at' => now()]);

        return $this->deleted(__('Todas las notificaciones marcadas como leídas.'));
    }
}
