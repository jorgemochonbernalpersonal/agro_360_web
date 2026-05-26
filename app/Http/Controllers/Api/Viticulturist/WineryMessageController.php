<?php

namespace App\Http\Controllers\Api\Viticulturist;

use App\Http\Controllers\Controller;
use App\Notifications\GrapePurchaseInvoiceIssuedNotification;
use App\Notifications\HarvestDeliveryAutoDisputedNotification;
use App\Notifications\HarvestDeliveryMatchedNotification;
use App\Notifications\HarvestDeliveryResolvedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WineryMessageController extends Controller
{
    private const WINERY_TYPES = [
        HarvestDeliveryMatchedNotification::class,
        HarvestDeliveryAutoDisputedNotification::class,
        HarvestDeliveryResolvedNotification::class,
        GrapePurchaseInvoiceIssuedNotification::class,
    ];

    // ─── GET /viticulturist/winery-messages ──────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $items = $user->notifications()
            ->whereIn('type', self::WINERY_TYPES)
            ->orderByDesc('created_at')
            ->paginate($this->resolvePerPage($request, 20));

        $unreadCount = $user->unreadNotifications()
            ->whereIn('type', self::WINERY_TYPES)
            ->count();

        $data = $items->getCollection()->map(fn ($n) => [
            'id'         => $n->id,
            'type'       => class_basename($n->type),
            'data'       => $n->data,
            'read_at'    => $n->read_at?->toIso8601String(),
            'created_at' => $n->created_at->toIso8601String(),
        ]);

        return response()->json([
            'data'         => $data,
            'unread_count' => $unreadCount,
            'meta'         => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'has_more'     => $items->hasMorePages(),
            ],
        ]);
    }

    // ─── POST /viticulturist/winery-messages/{id}/read ──────────────────────

    public function markRead(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasViticulturistAccess(), 403);

        $user->notifications()
            ->whereIn('type', self::WINERY_TYPES)
            ->where('id', $id)
            ->update(['read_at' => now()]);

        return response()->json(['message' => __('Mensaje marcado como leído.')]);
    }
}
